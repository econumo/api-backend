<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\AccountAccessInvite;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\AccountAccessInviteRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountAccessInvite|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountAccessInvite|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountAccessInvite[]    findAll()
 * @method AccountAccessInvite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountAccessInviteRepository extends ServiceEntityRepository implements AccountAccessInviteRepositoryInterface
{
    use SaveEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountAccessInvite::class);
    }

    public function get(Id $accountId, Id $recipientId): AccountAccessInvite
    {
        $item = $this->findOneBy([
            'account' => $this->getEntityManager()->getReference(Account::class, $accountId),
            'recipient' => $this->getEntityManager()->getReference(User::class, $recipientId)
        ]);
        if (!$item instanceof AccountAccessInvite) {
            throw new NotFoundException('AccountAccessInvite not found');
        }

        return $item;
    }

    public function delete(AccountAccessInvite $invite): void
    {
        $this->getEntityManager()->remove($invite);
        $this->getEntityManager()->flush();
    }

    public function getByUserAndCode(Id $userId, string $code): AccountAccessInvite
    {
        $item = $this->findOneBy(
            [
                'recipient' => $this->getEntityManager()->getReference(User::class, $userId),
                'code' => $code
            ]
        );
        if (!$item instanceof AccountAccessInvite) {
            throw new NotFoundException('AccountAccessInvite not found');
        }

        return $item;
    }

    public function getUnacceptedByUser(Id $userId): array
    {
        return $this->findBy(['owner' => $this->getEntityManager()->getReference(User::class, $userId)]);
    }
}
