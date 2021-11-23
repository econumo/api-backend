<?php

declare(strict_types=1);

namespace App\Application\Account\Assembler;

use App\Application\Account\Dto\CreateAccountV1RequestDto;
use App\Application\Account\Dto\CreateAccountV1ResultDto;
use App\Domain\Entity\Account;
use App\Domain\Entity\ValueObject\Id;

class CreateAccountV1ResultAssembler
{
    private AccountToDtoV1ResultAssembler $accountToDtoV1ResultAssembler;

    public function __construct(AccountToDtoV1ResultAssembler $accountToDtoV1ResultAssembler)
    {
        $this->accountToDtoV1ResultAssembler = $accountToDtoV1ResultAssembler;
    }

    public function assemble(
        CreateAccountV1RequestDto $dto,
        Id $userId,
        Account $account
    ): CreateAccountV1ResultDto {
        $result = new CreateAccountV1ResultDto();
        $result->item = $this->accountToDtoV1ResultAssembler->assemble($userId, $account);

        return $result;
    }
}