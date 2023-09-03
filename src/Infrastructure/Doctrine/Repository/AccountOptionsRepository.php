<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\AccountOptions;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\AccountOptionsRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountOptions[]    findAll()
 * @method AccountOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountOptionsRepository extends ServiceEntityRepository implements AccountOptionsRepositoryInterface
{
    use SaveEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountOptions::class);
    }

    public function getByUserId(Id $userId): array
    {
        $builder = $this->createQueryBuilder('ao');
        return $builder
            ->where('ao.user = :user')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->orderBy('ao.position', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function get(Id $accountId, Id $userId): AccountOptions
    {
        $item = $this->findOneBy([
            'account' => $this->getEntityManager()->getReference(Account::class, $accountId),
            'user' => $this->getEntityManager()->getReference(User::class, $userId)
        ]);
        if (!$item instanceof AccountOptions) {
            throw new NotFoundException(
                sprintf('AccountOptions for account_id %s user_id %s not found', $accountId, $userId)
            );
        }

        return $item;
    }

    public function delete(AccountOptions $options): void
    {
        $this->getEntityManager()->remove($options);
        $this->getEntityManager()->flush();
    }
}
