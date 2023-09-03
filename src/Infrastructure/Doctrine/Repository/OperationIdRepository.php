<?php
declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Infrastructure\Doctrine\Entity\OperationId;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OperationId|null find($id, $lockMode = null, $lockVersion = null)
 * @method OperationId|null findOneBy(array $criteria, array $orderBy = null)
 * @method OperationId[]    findAll()
 * @method OperationId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationIdRepository extends ServiceEntityRepository
{
    use SaveEntityTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OperationId::class);
    }

    public function get(Id $id): OperationId
    {
        $item = $this->find($id);
        if (!$item instanceof OperationId) {
            throw new NotFoundException(sprintf('OperationId %s not found', $id));
        }

        return $item;
    }

    public function remove(OperationId $operationId): void
    {
        $this->getEntityManager()->remove($operationId);
        $this->getEntityManager()->flush();
    }
}
