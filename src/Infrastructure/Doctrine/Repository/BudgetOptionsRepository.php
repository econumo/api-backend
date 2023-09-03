<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Budget;
use App\Domain\Entity\BudgetOptions;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\BudgetOptionsRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BudgetOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method BudgetOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method BudgetOptions[]    findAll()
 * @method BudgetOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetOptionsRepository extends ServiceEntityRepository implements BudgetOptionsRepositoryInterface
{
    use SaveEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetOptions::class);
    }

    public function getByUserId(Id $userId): array
    {
        $builder = $this->createQueryBuilder('bo');
        return $builder
            ->where('bo.user = :user')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->orderBy('bo.position', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function get(Id $budgetId, Id $userId): BudgetOptions
    {
        $item = $this->findOneBy([
            'budget' => $this->getEntityManager()->getReference(Budget::class, $budgetId),
            'user' => $this->getEntityManager()->getReference(User::class, $userId)
        ]);
        if (!$item instanceof BudgetOptions) {
            throw new NotFoundException(
                sprintf('BudgetOptions for budget_id %s user_id %s not found', $budgetId, $userId)
            );
        }

        return $item;
    }

    public function delete(BudgetOptions $options): void
    {
        $this->getEntityManager()->remove($options);
        $this->getEntityManager()->flush();
    }
}
