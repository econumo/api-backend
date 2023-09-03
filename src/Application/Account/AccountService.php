<?php

declare(strict_types=1);

namespace App\Application\Account;

use DateTimeImmutable;
use App\Application\Account\Assembler\CreateAccountV1ResultAssembler;
use App\Application\Account\Assembler\DeleteAccountV1ResultAssembler;
use App\Application\Account\Assembler\UpdateAccountV1ResultAssembler;
use App\Application\Account\Dto\CreateAccountV1RequestDto;
use App\Application\Account\Dto\CreateAccountV1ResultDto;
use App\Application\Account\Dto\DeleteAccountV1RequestDto;
use App\Application\Account\Dto\DeleteAccountV1ResultDto;
use App\Application\Account\Dto\UpdateAccountV1RequestDto;
use App\Application\Account\Dto\UpdateAccountV1ResultDto;
use App\Application\Exception\AccessDeniedException;
use App\Domain\Entity\ValueObject\AccountName;
use App\Domain\Entity\ValueObject\Icon;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Service\AccountAccessServiceInterface;
use App\Domain\Service\AccountServiceInterface;
use App\Domain\Service\Connection\ConnectionAccountServiceInterface;
use App\Domain\Service\Dto\AccountDto;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountService
{
    public function __construct(private readonly CreateAccountV1ResultAssembler $createAccountV1ResultAssembler, private readonly AccountServiceInterface $accountService, private readonly DeleteAccountV1ResultAssembler $deleteAccountV1ResultAssembler, private readonly UpdateAccountV1ResultAssembler $updateAccountV1ResultAssembler, private readonly AccountRepositoryInterface $accountRepository, private readonly AccountAccessServiceInterface $accountAccessService, private readonly TranslatorInterface $translator, private readonly ConnectionAccountServiceInterface $connectionAccountService)
    {
    }

    public function createAccount(
        CreateAccountV1RequestDto $dto,
        Id $userId
    ): CreateAccountV1ResultDto {
        $accountDto = new AccountDto();
        $accountDto->userId = $userId;
        $accountDto->name = $dto->name;
        $accountDto->currencyId = new Id($dto->currencyId);
        $accountDto->balance = $dto->balance;
        $accountDto->icon = $dto->icon;
        $accountDto->folderId = new Id($dto->folderId);

        $account = $this->accountService->create($accountDto);
        return $this->createAccountV1ResultAssembler->assemble($dto, $userId, $account);
    }

    public function deleteAccount(
        DeleteAccountV1RequestDto $dto,
        Id $userId
    ): DeleteAccountV1ResultDto {
        $accountId = new Id($dto->id);
        if (!$this->accountAccessService->canDeleteAccount($userId, $accountId)) {
            throw new AccessDeniedException();
        }

        $account = $this->accountRepository->get($accountId);
        if ($account->getUserId()->isEqual($userId)) {
            $this->accountService->delete($accountId);
        } else {
            $this->connectionAccountService->revokeAccountAccess($userId, $accountId);
        }

        return $this->deleteAccountV1ResultAssembler->assemble($dto);
    }

    public function updateAccount(
        UpdateAccountV1RequestDto $dto,
        Id $userId
    ): UpdateAccountV1ResultDto {
        $accountId = new Id($dto->id);
        if (!$this->accountAccessService->canUpdateAccount($userId, $accountId)) {
            throw new AccessDeniedException();
        }

        $this->accountService->update($userId, $accountId, new AccountName($dto->name), new Icon($dto->icon));
        $updatedAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dto->updatedAt);
        $transaction = $this->accountService->updateBalance($accountId, $dto->balance, $updatedAt, $this->translator->trans('account.correction.message'));
        $account = $this->accountRepository->get($accountId);
        return $this->updateAccountV1ResultAssembler->assemble($dto, $userId, $account, $transaction);
    }
}
