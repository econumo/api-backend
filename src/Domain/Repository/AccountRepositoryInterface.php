<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\ValueObject\Id;

interface AccountRepositoryInterface
{
    public function getNextIdentity(): Id;

    /**
     * @return Account[]
     */
    public function getAvailableForUserId(Id $userId): array;

    /**
     * @return Account[]
     */
    public function getUserAccounts(Id $userId): array;

    public function get(Id $id): Account;

    /**
     * @param Account[] $items
     */
    public function save(array $items): void;

    public function delete(Id $id): void;

    public function getReference(Id $id): Account;
}
