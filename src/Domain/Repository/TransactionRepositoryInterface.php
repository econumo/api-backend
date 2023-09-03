<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Transaction;
use App\Domain\Entity\ValueObject\Id;
use DateTimeInterface;

interface TransactionRepositoryInterface
{
    public function getNextIdentity(): Id;

    /**
     * @return Transaction[]
     */
    public function findByAccountId(Id $accountId): array;

    /**
     * @param Transaction[] $items
     */
    public function save(array $items): void;

    /**
     * @param Id[] $excludeAccounts
     * @return Transaction[]
     */
    public function findAvailableForUserId(Id $userId, array $excludeAccounts = []): array;

    /**
     * @return Transaction[]
     */
    public function findChanges(Id $userId, DateTimeInterface $lastUpdate): array;

    public function get(Id $id): Transaction;

    public function delete(Transaction $transaction): void;

    public function replaceCategory(Id $oldCategoryId, Id $newCategoryId): void;

    public function calculateTotalIncome(Id $userId, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): float;

    public function calculateTotalExpenses(Id $userId, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): float;

    public function calculateAmount(array $categoryIds, array $tagIds, bool $excludeTags, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): float;
}
