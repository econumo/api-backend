<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Folder;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\FolderRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\NextIdentityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * @method Folder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Folder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folder[]    findAll()
 * @method Folder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderRepository extends ServiceEntityRepository implements FolderRepositoryInterface
{
    use NextIdentityTrait;
    use SaveEntityTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Folder::class);
    }

    /**
     * @inheritDoc
     */
    public function getByUserId(Id $userId): array
    {
        return $this->findBy(['user' => $this->getEntityManager()->getReference(User::class, $userId)]);
    }

    public function get(Id $id): Folder
    {
        $item = $this->find($id);
        if (!$item instanceof Folder) {
            throw new NotFoundException(sprintf('Folder with ID %s not found', $id));
        }

        return $item;
    }

    public function delete(Folder $folder): void
    {
        $this->getEntityManager()->remove($folder);
        $this->getEntityManager()->flush();
    }

    public function isUserHasMoreThanOneFolder(Id $userId): bool
    {
        return 1 < count($this->getByUserId($userId));
    }

    public function getLastFolder(Id $userId): Folder
    {
        $builder = $this->createQueryBuilder('f');
        $item = $builder
            ->where('f.user = :user')
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId))
            ->orderBy('f.position', Criteria::DESC)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
        if (!$item instanceof Folder) {
            throw new NotFoundException('Folder not found');
        }

        return $item;
    }
}
