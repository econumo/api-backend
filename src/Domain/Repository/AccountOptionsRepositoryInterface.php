<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\AccountOptions;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;

interface AccountOptionsRepositoryInterface
{
    /**
     * @return AccountOptions[]
     */
    public function getByUserId(Id $userId): array;

    /**
     * @throws NotFoundException
     */
    public function get(Id $accountId, Id $userId): AccountOptions;

    public function delete(AccountOptions $options): void;

    /**
     * @param AccountOptions[] $items
     */
    public function save(array $items): void;
}
