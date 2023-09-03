<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Budget;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\BudgetRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\DeleteEntityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\NextIdentityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Budget|null find($id, $lockMode = null, $lockVersion = null)
 * @method Budget|null findOneBy(array $criteria, array $orderBy = null)
 * @method Budget[]    findAll()
 * @method Budget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetRepository extends ServiceEntityRepository implements BudgetRepositoryInterface
{
    use SaveEntityTrait;
    use NextIdentityTrait;
    use DeleteEntityTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    /**
     * @inheritDoc
     */
    public function getAvailableForUserId(Id $userId): array
    {
        $builder = $this->createQueryBuilder('b');
        return $builder
            ->select('b')
            ->leftJoin('b.sharedAccess', 'u')
            ->where($builder->expr()->orX(
                $builder->expr()->eq('b.user', ':user'),
                $builder->expr()->eq('u', ':user'),
            ))
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->getQuery()
            ->getResult();
    }

    public function get(Id $id): Budget
    {
        $item = $this->find($id);
        if (!$item instanceof Budget) {
            throw new NotFoundException(sprintf('Budget with ID %s not found', $id));
        }

        return $item;
    }

    public function getReference(Id $id): Budget
    {
        return $this->getEntityManager()->getReference(Budget::class, $id);
    }
}
