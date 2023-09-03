<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\AccountAccess;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\AccountAccessRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountAccess[]    findAll()
 * @method AccountAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountAccessRepository extends ServiceEntityRepository implements AccountAccessRepositoryInterface
{
    use SaveEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountAccess::class);
    }

    /**
     * @inheritDoc
     */
    public function findByUserId(Id $userId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->orderBy('a.position', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

    public function get(Id $accountId, Id $userId): AccountAccess
    {
        $item = $this->findOneBy([
            'account' => $this->getEntityManager()->getReference(Account::class, $accountId),
            'user' => $this->getEntityManager()->getReference(User::class, $userId)
        ]);
        if (!$item instanceof AccountAccess) {
            throw new NotFoundException('AccountAccess not found');
        }

        return $item;
    }

    public function delete(AccountAccess $accountAccess): void
    {
        $this->getEntityManager()->remove($accountAccess);
        $this->getEntityManager()->flush();
    }

    /**
     * @inheritDoc
     */
    public function getByAccount(Id $accountId): array
    {
        return $this->findBy(['account' => $this->getEntityManager()->getReference(Account::class, $accountId)]);
    }

    /**
     * @inheritDoc
     */
    public function getOwnedByUser(Id $userId): array
    {
        $dql = <<<'DQL'
SELECT a.id FROM App\Domain\Entity\AccountAccess aa
JOIN App\Domain\Entity\Account a WITH a = aa.account AND aa.user = :user
GROUP BY a.id
DQL;
        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId));
        $accounts = array_map(fn($id): ?Account => $this->getEntityManager()->getReference(Account::class, new Id($id)), array_column($query->getScalarResult(), 'id'));

        return $this->findBy(['account' => $accounts]);
    }

    /**
     * @inheritDoc
     */
    public function getReceivedAccess(Id $userId): array
    {
        return $this->findBy([
            'user' => $this->getEntityManager()->getReference(User::class, $userId)
        ]);
    }

    public function getIssuedAccess(Id $userId): array
    {
        $dql = <<<'DQL'
SELECT a.id FROM App\Domain\Entity\AccountAccess aa
JOIN App\Domain\Entity\Account a WITH a = aa.account AND a.user = :user
GROUP BY a.id
DQL;
        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId));
        $accounts = array_map(fn($id): ?Account => $this->getEntityManager()->getReference(Account::class, new Id($id)), array_column($query->getScalarResult(), 'id'));

        return $this->findBy(['account' => $accounts]);
    }
}
