<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Tag;
use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\TagRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository implements TagRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function getNextIdentity(): Id
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $uuid = Uuid::uuid4();

        return new Id($uuid->toString());
    }

    /**
     * @inheritDoc
     */
    public function findByUserId(Id $userId): array
    {
        $dql =<<<'DQL'
SELECT u.id FROM App\Domain\Entity\User u
LEFT JOIN App\Domain\Entity\AccountAccess aa WITH aa.user = :user
LEFT JOIN App\Domain\Entity\Account a WITH a = aa.account
GROUP BY u.id
DQL;
        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('user', $this->getEntityManager()->getReference(User::class, $userId));
        $ids = array_column($query->getScalarResult(), 'id');
        $ids[] = $userId->getValue();
        $users = array_map(function ($id) {
            return $this->getEntityManager()->getReference(User::class, new Id($id));
        }, array_unique($ids));

        return $this->createQueryBuilder('c')
            ->andWhere('c.user IN(:users)')
            ->setParameter('users', $users)
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByOwnerId(Id $userId): array
    {
        return $this->findBy(['user' => $this->getEntityManager()->getReference(User::class, $userId)]);
    }

    public function get(Id $id): Tag
    {
        /** @var Tag|null $item */
        $item = $this->find($id);
        if ($item === null) {
            throw new NotFoundException(sprintf('Tag with ID %s not found', $id));
        }

        return $item;
    }

    public function save(Tag ...$tags): void
    {
        try {
            foreach ($tags as $tag) {
                $this->getEntityManager()->persist($tag);
            }
            $this->getEntityManager()->flush();
        } catch (ORMException | ORMInvalidArgumentException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getReference(Id $id): Tag
    {
        return $this->getEntityManager()->getReference(Tag::class, $id);
    }

    public function delete(Tag $tag): void
    {
        $this->getEntityManager()->remove($tag);
        $this->getEntityManager()->flush();
    }
}
