<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Currency;
use App\Domain\Entity\ValueObject\CurrencyCode;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\CurrencyRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\NextIdentityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * @method Currency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Currency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Currency[]    findAll()
 * @method Currency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRepository extends ServiceEntityRepository implements CurrencyRepositoryInterface
{
    use SaveEntityTrait;
    use NextIdentityTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    public function get(Id $id): Currency
    {
        $item = $this->find($id);
        if (!$item instanceof Currency) {
            throw new NotFoundException(sprintf('Currency with identifier %s not found', $item));
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->findAll();
    }

    public function getReference(Id $id): Currency
    {
        return $this->getEntityManager()->getReference(Currency::class, $id);
    }

    public function getByCode(CurrencyCode $code): ?Currency
    {
        return $this->findOneBy(['code' => $code]);
    }
}
