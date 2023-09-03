<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Entity\UserOption;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Repository\UserOptionRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\NextIdentityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserOption[]    findAll()
 * @method UserOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserOptionRepository extends ServiceEntityRepository implements UserOptionRepositoryInterface
{
    use SaveEntityTrait;
    use NextIdentityTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserOption::class);
    }

    public function getReference(Id $id): UserOption
    {
        return $this->getEntityManager()->getReference(UserOption::class, $id);
    }

    public function delete(UserOption $userOption): void
    {
        $this->getEntityManager()->remove($userOption);
        $this->getEntityManager()->flush();
    }

    /**
     * @inheritDoc
     */
    public function findByUserId(Id $userId): array
    {
        return $this->findBy(['user' => $this->getEntityManager()->getReference(User::class, $userId)]);
    }
}
