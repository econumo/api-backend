<?php
declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\Entity\ValueObject\Email;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Entity\ValueObject\Identifier;

interface UserRepositoryInterface
{
    public function getNextIdentity(): Id;

    public function loadByIdentifier(Identifier $identifier): User;

    public function getByEmail(Email $email): User;

    /**
     * @param User[] $items
     */
    public function save(array $items): void;

    public function get(Id $id): User;

    public function getReference(Id $id): User;
}
