<?php

declare(strict_types=1);

namespace App\Application\Account\Assembler;

use App\Application\Account\Dto\GetCollectionV1RequestDto;
use App\Application\Account\Dto\GetCollectionV1ResultDto;
use App\Domain\Entity\Account;
use App\Domain\Entity\ValueObject\Id;

class GetCollectionV1ResultAssembler
{
    private AccountToDtoV1ResultAssembler $accountToDtoV1ResultAssembler;

    public function __construct(AccountToDtoV1ResultAssembler $accountToDtoV1ResultAssembler)
    {
        $this->accountToDtoV1ResultAssembler = $accountToDtoV1ResultAssembler;
    }

    /**
     * @param GetCollectionV1RequestDto $dto
     * @param Account[] $accounts
     * @return GetCollectionV1ResultDto
     */
    public function assemble(
        GetCollectionV1RequestDto $dto,
        Id $userId,
        array $accounts
    ): GetCollectionV1ResultDto {
        $result = new GetCollectionV1ResultDto();
        $result->items = [];
        foreach (array_reverse($accounts) as $account) {
            $result->items[] = $this->accountToDtoV1ResultAssembler->assemble($userId, $account);
        }

        return $result;
    }
}