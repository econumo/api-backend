<?php
declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\ConnectionInvite;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\ConnectionCode;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\ConnectionInviteRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConnectionInvite|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConnectionInvite|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConnectionInvite[]    findAll()
 * @method ConnectionInvite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConnectionInviteRepository extends ServiceEntityRepository implements ConnectionInviteRepositoryInterface
{
    use SaveEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConnectionInvite::class);
    }

    public function getByUser(Id $userId): ?ConnectionInvite
    {
        return $this->findOneBy([
            'user' => $this->getEntityManager()->getReference(User::class, $userId)
        ]);
    }

    public function delete(ConnectionInvite $item): void
    {
        $item->clearCode();
        $this->getEntityManager()->flush();
    }

    public function getByCode(ConnectionCode $code): ConnectionInvite
    {
        $item = $this->findOneBy(['code' => $code]);
        if (!$item instanceof ConnectionInvite) {
            throw new NotFoundException(sprintf('ConnectionCode with CODE %s not found', $code));
        }

        if ($item->isExpired()) {
            $this->delete($item);
            throw new NotFoundException(sprintf('ConnectionCode with CODE %s not found', $code));
        }

        return $item;
    }
}
