<?php

declare(strict_types=1);


namespace App\Domain\Service;


use Throwable;
use App\Domain\Entity\Account;
use App\Domain\Entity\AccountAccessInvite;
use App\Domain\Entity\ValueObject\AccountUserRole;
use App\Domain\Entity\ValueObject\Email;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\AccountAccessException;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Factory\AccountAccessFactoryInterface;
use App\Domain\Factory\AccountAccessInviteFactoryInterface;
use App\Domain\Factory\AccountOptionsFactoryInterface;
use App\Domain\Repository\AccountAccessInviteRepositoryInterface;
use App\Domain\Repository\AccountAccessRepositoryInterface;
use App\Domain\Repository\AccountOptionsRepositoryInterface;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;

class AccountAccessInviteService implements AccountAccessInviteServiceInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository, private readonly AccountAccessInviteRepositoryInterface $accountAccessInviteRepository, private readonly AccountAccessInviteFactoryInterface $accountAccessInviteFactory, private readonly AccountRepositoryInterface $accountRepository, private readonly AccountAccessFactoryInterface $accountAccessFactory, private readonly AccountAccessRepositoryInterface $accountAccessRepository, private readonly AntiCorruptionServiceInterface $antiCorruptionService, private readonly AccountOptionsFactoryInterface $accountOptionsFactory, private readonly AccountOptionsRepositoryInterface $accountOptionsRepository)
    {
    }

    public function generate(
        Id $userId,
        Id $accountId,
        Email $recipientUsername,
        AccountUserRole $role
    ): AccountAccessInvite {
        $account = $this->accountRepository->get($accountId);
        $recipient = $this->userRepository->getByEmail($recipientUsername);
        if ($userId->isEqual($recipient->getId())) {
            throw new AccountAccessException('Access for yourself is prohibited');
        }

        $this->antiCorruptionService->beginTransaction();
        try {
            try {
                $oldInvite = $this->accountAccessInviteRepository->get($accountId, $recipient->getId());
                $this->accountAccessInviteRepository->delete($oldInvite);
            } catch (NotFoundException) {
                // do nothing
            }

            $invite = $this->accountAccessInviteFactory->create(
                $accountId,
                $recipient->getId(),
                $account->getUserId(),
                $role
            );
            $this->accountAccessInviteRepository->save([$invite]);
            $this->antiCorruptionService->commit();
        } catch (Throwable $throwable) {
            $this->antiCorruptionService->rollback();
            throw $throwable;
        }

        return $invite;
    }

    public function accept(Id $userId, string $code): Account
    {
        $invite = $this->accountAccessInviteRepository->getByUserAndCode($userId, $code);
        $account = $this->accountRepository->get($invite->getAccountId());
        $this->antiCorruptionService->beginTransaction();
        try {
            $access = $this->accountAccessFactory->create(
                $account->getId(),
                $userId,
                $invite->getRole()
            );
            $this->accountAccessRepository->save([$access]);
            $this->accountAccessInviteRepository->delete($invite);

            $accountOptions = $this->accountOptionsFactory->create($account->getId(), $userId, 0);
            $this->accountOptionsRepository->save([$accountOptions]);

            $this->antiCorruptionService->commit();
        } catch (Throwable $throwable) {
            $this->antiCorruptionService->rollback();
            throw $throwable;
        }

        return $account;
    }
}
