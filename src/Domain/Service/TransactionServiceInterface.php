<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Transaction;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Service\Dto\TransactionDto;
use DateTimeInterface;

interface TransactionServiceInterface
{
    public function createTransaction(TransactionDto $transactionDto): Transaction;

    public function updateTransaction(Id $id, TransactionDto $transactionDto): Transaction;

    public function deleteTransaction(Transaction $transaction): void;

    public function updateBalance(Id $accountId, float $correction, DateTimeInterface $updatedAt, string $comment = ''): Transaction;

    /**
     * @param Id $userId
     * @return Transaction[]
     */
    public function getTransactionsForVisibleAccounts(Id $userId): array;
}
