<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\AccountAccess;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\DeleteEntityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\NextIdentityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository implements AccountRepositoryInterface
{
    use SaveEntityTrait;
    use NextIdentityTrait;
    use DeleteEntityTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @inheritDoc
     */
    public function getAvailableForUserId(Id $userId): array
    {
        $builder = $this->createQueryBuilder('a');
        return $builder
            ->select('a')
            ->leftJoin(AccountAccess::class, 'aa', Join::WITH, 'aa.account = a')
            ->where($builder->expr()->orX(
                $builder->expr()->eq('a.user', ':user'),
                $builder->expr()->eq('aa.user', ':user'),
            ))
            ->andWhere('a.isDeleted = false')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getUserAccounts(Id $userId): array
    {
        $builder = $this->createQueryBuilder('a');
        return $builder
            ->select('a')
            ->leftJoin(AccountAccess::class, 'aa', Join::WITH, 'aa.account = a')
            ->where($builder->expr()->orX(
                $builder->expr()->eq('a.user', ':user'),
                $builder->expr()->eq('aa.user', ':user'),
            ))
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->getQuery()
            ->getResult();
    }

    public function get(Id $id): Account
    {
        $item = $this->find($id);
        if (!$item instanceof Account) {
            throw new NotFoundException(sprintf('Account with ID %s not found', $id));
        }

        if ($item->isDeleted()) {
            throw new NotFoundException(sprintf('Account with ID %s not found', $id));
        }

        return $item;
    }

    public function getReference(Id $id): Account
    {
        return $this->getEntityManager()->getReference(Account::class, $id);
    }
}
