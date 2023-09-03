<?php
declare(strict_types=1);

namespace App\Domain\Factory;

use DateTimeInterface;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Service\Dto\TransactionDto;

interface TransactionFactoryInterface
{
    public function create(TransactionDto $dto): Transaction;

    public function createCorrection(Id $accountId, float $correction, DateTimeInterface $transactionDate, string $comment = ''): Transaction;
}
