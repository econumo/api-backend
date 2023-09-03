<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\BudgetOptions;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;

interface BudgetOptionsRepositoryInterface
{
    /**
     * @return BudgetOptions[]
     */
    public function getByUserId(Id $userId): array;

    /**
     * @throws NotFoundException
     */
    public function get(Id $budgetId, Id $userId): BudgetOptions;

    public function delete(BudgetOptions $options): void;

    /**
     * @param BudgetOptions[] $budgetOptions
     */
    public function save(array $budgetOptions): void;
}
