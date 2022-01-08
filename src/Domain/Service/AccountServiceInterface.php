<?php
declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Account;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Service\Dto\AccountDto;

interface AccountServiceInterface
{
    public function create(AccountDto $dto): Account;

    public function delete(Id $id): void;

    public function update(Id $userId, Id $accountId, string $name, string $icon = null): void;

    public function updateBalance(Id $accountId, float $balance, \DateTimeInterface $updatedAt, ?string $comment = ''): ?Transaction;

    public function order(Id $userId, Id $accountId, Id $folderId, int $position): void;
}
