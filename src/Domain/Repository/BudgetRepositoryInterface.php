<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Budget;
use App\Domain\Entity\ValueObject\Id;

interface BudgetRepositoryInterface
{
    public function getNextIdentity(): Id;

    /**
     * @return Budget[]
     */
    public function getAvailableForUserId(Id $userId): array;

    public function get(Id $id): Budget;

    /**
     * @param Budget[] $budgets
     */
    public function save(array $budgets): void;

    public function delete(Id $id): void;

    public function getReference(Id $id): Budget;
}
