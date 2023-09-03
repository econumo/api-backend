<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\PasswordUserRequest;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\PasswordUserRequestRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\NextIdentityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PasswordUserRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordUserRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordUserRequest[]    findAll()
 * @method PasswordUserRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordUserRequestRepository extends ServiceEntityRepository implements PasswordUserRequestRepositoryInterface
{
    use NextIdentityTrait;
    use SaveEntityTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordUserRequest::class);
    }

    public function getByCode(string $code): PasswordUserRequest
    {
        $item = $this->findOneBy(['code' => $code]);
        if (!$item instanceof PasswordUserRequest) {
            throw new NotFoundException(sprintf('PasswordUserRequest with ID %s not found', $code));
        }

        return $item;
    }
}
