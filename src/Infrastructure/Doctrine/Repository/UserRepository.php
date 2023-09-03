<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Email;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Entity\ValueObject\Identifier;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\Traits\NextIdentityTrait;
use App\Infrastructure\Doctrine\Repository\Traits\SaveEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserRepositoryInterface
{
    use SaveEntityTrait;
    use NextIdentityTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->updatePassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function loadByIdentifier(Identifier $identifier): User
    {
        $user = $this->findOneBy(['identifier' => $identifier->getValue()]);
        if (!$user instanceof User) {
            throw new NotFoundException(sprintf('User with identifier %s not found', $identifier));
        }

        return $user;
    }

    public function get(Id $id): User
    {
        $item = $this->find($id);
        if (!$item instanceof User) {
            throw new NotFoundException(sprintf('User with ID %s not found', $id));
        }

        return $item;
    }

    public function getByEmail(Email $email): User
    {
        $user = $this->findOneBy(['identifier' => $email->getValue()]);
        if (!$user instanceof User) {
            throw new NotFoundException(sprintf('User with email %s not found', $email));
        }

        return $user;
    }

    public function getReference(Id $id): User
    {
        return $this->getEntityManager()->getReference(User::class, $id);
    }
}
