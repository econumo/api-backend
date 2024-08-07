<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\TransactionRepositoryInterface;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository implements TransactionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getNextIdentity(): Id
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $uuid = Uuid::uuid4();

        return new Id($uuid->toString());
    }

    /**
     * @inheritDoc
     */
    public function findByAccountId(Id $accountId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.account = :account')
            ->orWhere('t.accountRecipient = :account')
            ->setParameter('account', $this->getEntityManager()->getReference(Account::class, $accountId))
            ->orderBy('t.spentAt', Criteria::DESC)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function save(array $transactions): void
    {
        try {
            foreach ($transactions as $transaction) {
                $this->getEntityManager()->persist($transaction);
            }

            $this->getEntityManager()->flush();
        } catch (ORMException | ORMInvalidArgumentException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(Id $id): Transaction
    {
        $item = $this->find($id);
        if (!$item instanceof Transaction) {
            throw new NotFoundException(sprintf('Transaction with ID %s not found', $id));
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function findAvailableForUserId(
        Id $userId,
        array $excludeAccounts = [],
        DateTimeInterface $periodStart = null,
        DateTimeInterface $periodEnd = null,
    ): array
    {
        $sharedAccountsQuery = $this->getEntityManager()
            ->createQuery('SELECT IDENTITY(aa.account) as accountId FROM App\Domain\Entity\AccountAccess aa WHERE aa.user = :user')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId));
        $sharedIds = array_column($sharedAccountsQuery->getScalarResult(), 'accountId');

        $accountsQuery = $this->getEntityManager()
            ->createQuery('SELECT a.id FROM App\Domain\Entity\Account a WHERE a.user = :user')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId));
        $userAccountIds = array_column($accountsQuery->getScalarResult(), 'id');
        $accounts = array_map(fn(string $id): ?Account => $this->getEntityManager()->getReference(Account::class, new Id($id)), array_unique([...$sharedIds, ...$userAccountIds]));
        $filteredAccounts = [];
        foreach ($accounts as $account) {
            $found = false;
            foreach ($excludeAccounts as $accountId) {
                if ($accountId->isEqual($account->getId())) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $filteredAccounts[] = $account;
            }
        }

        $query = $this->createQueryBuilder('t')
            ->where('t.account IN(:accounts) OR t.accountRecipient IN(:accounts)')
            ->setParameter('accounts', $filteredAccounts);
        if ($periodStart && $periodEnd) {
            $query->andWhere('t.spentAt >= :periodStart')
                ->andWhere('t.spentAt < :periodEnd')
                ->setParameter('periodStart', $periodStart)
                ->setParameter('periodEnd', $periodEnd);
        }

        return $query->getQuery()->getResult();
    }

    public function delete(Transaction $transaction): void
    {
        $this->getEntityManager()->remove($transaction);
        $this->getEntityManager()->flush();
    }

    public function replaceCategory(Id $oldCategoryId, Id $newCategoryId): void
    {
        $builder = $this->createQueryBuilder('t');
        $builder->update()
            ->set('t.category', ':newCategory')
            ->setParameter('newCategory', $this->getEntityManager()->getReference(Category::class, $newCategoryId))
            ->where('t.category = :oldCategory')
            ->setParameter('oldCategory', $this->getEntityManager()->getReference(Category::class, $oldCategoryId));
        $builder->getQuery()->execute();
    }

    public function findChanges(Id $userId, DateTimeInterface $lastUpdate): array
    {
        $sharedAccountsQuery = $this->getEntityManager()
            ->createQuery('SELECT IDENTITY(aa.account) as accountId FROM App\Domain\Entity\AccountAccess aa WHERE aa.user = :user')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId));
        $sharedIds = array_column($sharedAccountsQuery->getScalarResult(), 'accountId');

        $accountsQuery = $this->getEntityManager()
            ->createQuery('SELECT a.id FROM App\Domain\Entity\Account a WHERE a.user = :user')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId));
        $userAccountIds = array_column($accountsQuery->getScalarResult(), 'id');
        $accounts = array_map(fn(string $id): ?Account => $this->getEntityManager()->getReference(Account::class, new Id($id)), array_unique([...$sharedIds, ...$userAccountIds]));

        $query = $this->createQueryBuilder('t')
            ->where('t.account IN(:accounts) OR t.accountRecipient IN(:accounts)')
            ->andWhere('t.updatedAt > :lastUpdate')
            ->setParameter('accounts', $accounts)
            ->setParameter('lastUpdate', $lastUpdate);

        return $query->getQuery()->getResult();
    }

    public function getBalance(Id $accountId, DateTimeInterface $date): float
    {
        $dateFormatted = $date->format('Y-m-d H:i:s');
        $accountIdFormatted = $accountId->getValue();
        $sql = <<<SQL
SELECT COALESCE(incomes, 0) + COALESCE(transfer_incomes, 0) - COALESCE(expenses, 0) - COALESCE(transfer_expenses, 0) as balance
FROM (SELECT tmp.account_id, SUM(tmp.expenses) as expenses, SUM(tmp.incomes) as incomes, SUM(tmp.transfer_expenses) as transfer_expenses, SUM(tmp.transfer_incomes) as transfer_incomes
      FROM (SELECT tr1.account_id,
                   (SELECT SUM(t1.amount) FROM transactions t1 WHERE t1.account_id = tr1.account_id AND t1.type = 0 AND t1.spent_at < '{$dateFormatted}') as expenses,
                   (SELECT SUM(t2.amount) FROM transactions t2 WHERE t2.account_id = tr1.account_id AND t2.type = 1 AND t2.spent_at < '{$dateFormatted}') as incomes,
                   (SELECT SUM(t3.amount) FROM transactions t3 WHERE t3.account_id = tr1.account_id AND t3.type = 2 AND t3.spent_at < '{$dateFormatted}') as transfer_expenses,
                   NULL as transfer_incomes
            FROM transactions tr1
            WHERE tr1.account_id = '{$accountIdFormatted}'
            GROUP BY tr1.account_id
            UNION ALL
            SELECT tr2.account_recipient_id as account_id, NULL as expenses, NULL as incomes, NULL as transfer_expenses, (SELECT SUM(t4.amount_recipient) FROM transactions t4 WHERE t4.account_recipient_id = tr2.account_recipient_id AND t4.type = 2 AND t4.spent_at < '{$dateFormatted}') as transfer_incomes
            FROM transactions tr2
            WHERE tr2.account_recipient_id IS NOT NULL AND tr2.account_recipient_id = '{$accountIdFormatted}' AND tr2.spent_at < '{$dateFormatted}'
            GROUP BY tr2.account_recipient_id) tmp
      GROUP BY tmp.account_id) bln
SQL;
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('balance', 'balance', 'float');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        try {
            $result = (float) $query->getSingleScalarResult();
        } catch (NoResultException $e) {
            $result = 0.0;
        }
        return $result;
    }

    public function countSpendingForCategories(
        array $categoryIds,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ): array {
        if (count($categoryIds) === 0) {
            return [];
        }

        $parameters = [];
        foreach ($categoryIds as $categoryId) {
            $parameters[] = $categoryId->getValue();
        }
        $categoriesString = implode("', '", $parameters);
        $startDateString = $startDate->format('Y-m-d H:i:s');
        $endDateString = $endDate->format('Y-m-d H:i:s');
        $sql =<<<SQL
SELECT sum(t.amount) as amount, t.category_id, a.currency_id FROM transactions t 
LEFT JOIN accounts a ON t.account_id = a.id
WHERE t.category_id IN ('{$categoriesString}') AND t.spent_at >= '{$startDateString}' AND t.spent_at < '{$endDateString}' AND t.tag_id IS NULL
GROUP BY a.currency_id, t.category_id
SQL;
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('category_id', 'category_id');
        $rsm->addScalarResult('currency_id', 'currency_id');
        $rsm->addScalarResult('amount', 'amount', 'float');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function countSpendingForTags(
        array $tagsIds,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ): array {
        if (count($tagsIds) === 0) {
            return [];
        }

        $parameters = [];
        foreach ($tagsIds as $tagId) {
            $parameters[] = $tagId->getValue();
        }
        $tagsString = implode("', '", $parameters);
        $startDateString = $startDate->format('Y-m-d H:i:s');
        $endDateString = $endDate->format('Y-m-d H:i:s');
        $sql =<<<SQL
SELECT sum(t.amount) as amount, t.tag_id, a.currency_id FROM transactions t 
LEFT JOIN accounts a ON t.account_id = a.id
WHERE t.tag_id IN ('{$tagsString}') AND t.spent_at >= '{$startDateString}' AND t.spent_at < '{$endDateString}'
GROUP BY a.currency_id, t.tag_id
SQL;
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('tag_id', 'tag_id');
        $rsm->addScalarResult('currency_id', 'currency_id');
        $rsm->addScalarResult('amount', 'amount', 'float');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function getAccountsReport(array $accountIds, DateTimeInterface $periodStart, DateTimeInterface $periodEnd): array
    {
        if ($accountIds === []) {
            return [];
        }

        $accounts = [];
        foreach ($accountIds as $accountId) {
            $accounts[] = $accountId->getValue();
        }
        $accountsString = implode("', '", $accounts);
        $periodStartString = $periodStart->format('Y-m-d H:i:s');
        $periodEndString = $periodEnd->format('Y-m-d H:i:s');
        $sql =<<<SQL
SELECT a.id as account_id,
       a.currency_id,
       COALESCE(incomes, 0) as incomes,
       COALESCE(transfer_incomes, 0) as transfer_incomes,
       COALESCE(exchange_incomes, 0) as exchange_incomes,
       COALESCE(expenses, 0) as expenses,
       COALESCE(transfer_expenses, 0) as transfer_expenses,
       COALESCE(exchange_expenses, 0) as exchange_expenses
FROM accounts a
       LEFT JOIN (
           SELECT tmp.account_id, SUM(tmp.expenses) as expenses, SUM(tmp.incomes) as incomes, SUM(tmp.transfer_expenses) as transfer_expenses, SUM(tmp.transfer_incomes) as transfer_incomes, SUM(tmp.exchange_expenses) as exchange_expenses, SUM(tmp.exchange_incomes) as exchange_incomes FROM (
                SELECT tr1.account_id,
                       (SELECT SUM(t1.amount) FROM transactions t1 WHERE t1.account_id = tr1.account_id AND t1.type = 0 AND t1.spent_at >= '{$periodStartString}' AND t1.spent_at < '{$periodEndString}') as expenses,
                       (SELECT SUM(t2.amount) FROM transactions t2 WHERE t2.account_id = tr1.account_id AND t2.type = 1 AND t2.spent_at >= '{$periodStartString}' AND t2.spent_at < '{$periodEndString}') as incomes,
                       (SELECT SUM(t3.amount) FROM transactions t3 WHERE t3.account_id = tr1.account_id AND t3.type = 2 AND t3.spent_at >= '{$periodStartString}' AND t3.spent_at < '{$periodEndString}') as transfer_expenses,
                       NULL as transfer_incomes,
                       NULL as exchange_incomes,
                       (SELECT SUM(t4.amount) FROM transactions t4 WHERE t4.account_id = tr1.account_id AND t4.type = 2 AND t4.amount != t4.amount_recipient AND t4.spent_at >= '{$periodStartString}' AND t4.spent_at < '{$periodEndString}') as exchange_expenses
                FROM transactions tr1
                WHERE tr1.spent_at >= '{$periodStartString}' AND tr1.spent_at < '{$periodEndString}'
                GROUP BY tr1.account_id
                UNION ALL
                SELECT tr2.account_recipient_id as account_id,
                       NULL as expenses,
                       NULL as incomes,
                       NULL as transfer_expenses,
                       (SELECT SUM(t5.amount_recipient) FROM transactions t5 WHERE t5.account_recipient_id = tr2.account_recipient_id AND t5.type = 2 AND t5.spent_at >= '{$periodStartString}' AND t5.spent_at < '{$periodEndString}') as transfer_incomes,
                       (SELECT SUM(t6.amount_recipient) FROM transactions t6 WHERE t6.account_recipient_id = tr2.account_recipient_id AND t6.type = 2 AND t6.amount != t6.amount_recipient AND t6.spent_at >= '{$periodStartString}' AND t6.spent_at < '{$periodEndString}') as exchange_incomes,
                       NULL as exchange_expenses
                FROM transactions tr2
                WHERE tr2.account_recipient_id IS NOT NULL AND tr2.spent_at >= '{$periodStartString}' AND tr2.spent_at < '{$periodEndString}'
                GROUP BY tr2.account_recipient_id) tmp GROUP BY tmp.account_id
       ) t ON a.id = t.account_id AND a.id IN ('{$accountsString}');
SQL;
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('account_id', 'account_id');
        $rsm->addScalarResult('currency_id', 'currency_id');
        $rsm->addScalarResult('incomes', 'incomes', 'float');
        $rsm->addScalarResult('transfer_incomes', 'transfer_incomes', 'float');
        $rsm->addScalarResult('exchange_incomes', 'exchange_incomes', 'float');
        $rsm->addScalarResult('expenses', 'expenses', 'float');
        $rsm->addScalarResult('transfer_expenses', 'transfer_expenses', 'float');
        $rsm->addScalarResult('exchange_expenses', 'exchange_expenses', 'float');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function getHoardsReport(
        array $reportAccountIds,
        array $hoardAccountIds,
        DateTimeInterface $periodStart,
        DateTimeInterface $periodEnd
    ): array {
        if ($reportAccountIds === [] || $hoardAccountIds === []) {
            return [];
        }

        $reportAccounts = [];
        foreach ($reportAccountIds as $accountId) {
            $reportAccounts[] = $accountId->getValue();
        }
        $hoardAccounts = [];
        foreach ($hoardAccountIds as $accountId) {
            $hoardAccounts[] = $accountId->getValue();
        }
        $reportAccountsString = implode("', '", $reportAccounts);
        $hoardAccountsString = implode("', '", $hoardAccounts);
        $periodStartString = $periodStart->format('Y-m-d H:i:s');
        $periodEndString = $periodEnd->format('Y-m-d H:i:s');

        $sql =<<<SQL
SELECT SUM(t.amount_recipient) as amount, a.currency_id FROM transactions t
LEFT JOIN accounts a ON t.account_recipient_id = a.id
WHERE t.amount = t.amount_recipient AND t.account_recipient_id IN ('{$hoardAccountsString}') AND t.account_id IN ('{$reportAccountsString}') AND t.type = 2 AND t.spent_at >= '{$periodStartString}' AND t.spent_at < '{$periodEndString}'
GROUP BY a.currency_id;
SQL;
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('currency_id', 'currency_id');
        $rsm->addScalarResult('amount', 'amount', 'float');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $toHoard = $query->getResult();

        $sql =<<<SQL
SELECT SUM(t.amount) as amount, a.currency_id FROM transactions t
LEFT JOIN accounts a ON t.account_id = a.id
WHERE t.amount = t.amount_recipient AND t.account_recipient_id IN ('{$reportAccountsString}') AND t.account_id IN ('{$hoardAccountsString}') AND t.type = 2 AND t.spent_at >= '{$periodStartString}' AND t.spent_at < '{$periodEndString}'
GROUP BY a.currency_id;
SQL;
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('currency_id', 'currency_id');
        $rsm->addScalarResult('amount', 'amount', 'float');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $fromHoard = $query->getResult();

        $result = [];
        foreach ($toHoard as $item) {
            if (!isset($result[$item['currency_id']])) {
                $result[$item['currency_id']] = [
                    'to_hoards' => 0.0,
                    'from_hoards' => 0.0,
                ];
            }
            $result[$item['currency_id']]['to_hoards'] += (float)$item['amount'];
        }
        foreach ($fromHoard as $item) {
            if (!isset($result[$item['currency_id']])) {
                $result[$item['currency_id']] = [
                    'to_hoards' => 0.0,
                    'from_hoards' => 0.0,
                ];
            }
            $result[$item['currency_id']]['from_hoards'] += (float)$item['amount'];
        }

        return $result;
    }
}
