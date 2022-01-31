<?php

declare(strict_types=1);


namespace App\Domain\Service\Connection;

use App\Domain\Entity\ValueObject\Id;
use App\Domain\Repository\AccountAccessRepositoryInterface;

class ConnectionAccountService implements ConnectionAccountServiceInterface
{
    private AccountAccessRepositoryInterface $accountAccessRepository;

    public function __construct(AccountAccessRepositoryInterface $accountAccessRepository)
    {
        $this->accountAccessRepository = $accountAccessRepository;
    }

    public function deleteAccountAccess(Id $userId, Id $sharedAccountId): void
    {
        $this->accountAccessRepository->delete($sharedAccountId, $userId);
    }

    /**
     * @inheritDoc
     */
    public function getReceivedAccountAccess(Id $userId): array
    {
        return $this->accountAccessRepository->getReceivedAccess($userId);
    }

    /**
     * @inheritDoc
     */
    public function getIssuedAccountAccess(Id $userId): array
    {
        return $this->accountAccessRepository->getIssuedAccess($userId);
    }
}