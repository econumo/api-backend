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
use App\Infrastructure\Doctrine\Repository\Traits\NextIdentityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository implements TransactionRepositoryInterface
{
    use NextIdentityTrait;
    use SaveEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
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
    public function findAvailableForUserId(Id $userId, array $excludeAccounts = []): array
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

    public function calculateTotalIncome(Id $userId, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): float
    {
        $result = $this->getEntityManager()
            ->createQuery('SELECT COALESCE(SUM(t.amount)) as amount FROM App\Domain\Entity\Transaction t WHERE t.type = 1 AND t.user = :user AND t.spentAt >= :dateStart AND t.spentAt < :dateEnd')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->getSingleScalarResult();
        return (float)$result;
    }

    public function calculateTotalExpenses(Id $userId, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): float
    {
        $result = $this->getEntityManager()
            ->createQuery('SELECT COALESCE(SUM(t.amount)) as amount FROM App\Domain\Entity\Transaction t WHERE t.type = 0 AND t.user = :user AND t.spentAt >= :dateStart AND t.spentAt < :dateEnd')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->getSingleScalarResult();
        return (float)$result;
    }

    public function calculateAmount(
        array $categoryIds,
        array $tagIds,
        bool $excludeTags,
        DateTimeInterface $dateStart,
        DateTimeInterface $dateEnd
    ): float {
        if ($tagIds === []) {
            $result = $this->getEntityManager()
                ->createQuery('SELECT COALESCE(SUM(t.amount)) as amount FROM App\Domain\Entity\Transaction t WHERE t.category IN (:categoryIds) AND t.spentAt >= :dateStart AND t.spentAt < :dateEnd')
                ->setParameter('categoryIds', $categoryIds)
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd)
                ->getSingleScalarResult();
        } elseif ($excludeTags) {
            $result = $this->getEntityManager()
                ->createQuery('SELECT COALESCE(SUM(t.amount)) as amount FROM App\Domain\Entity\Transaction t WHERE t.category IN (:categoryIds) AND t.tag IN (:tagIds) AND t.spentAt >= :dateStart AND t.spentAt < :dateEnd')
                ->setParameter('tagIds', $tagIds)
                ->setParameter('categoryIds', $categoryIds)
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd)
                ->getSingleScalarResult();
        } else {
            $result = $this->getEntityManager()
                ->createQuery('SELECT COALESCE(SUM(t.amount)) as amount FROM App\Domain\Entity\Transaction t WHERE t.category IN (:categoryIds) AND t.tag NOT IN (:tagIds) AND t.spentAt >= :dateStart AND t.spentAt < :dateEnd')
                ->setParameter('tagIds', $tagIds)
                ->setParameter('categoryIds', $categoryIds)
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd)
                ->getSingleScalarResult();
        }

        return (float)$result;
    }
}
