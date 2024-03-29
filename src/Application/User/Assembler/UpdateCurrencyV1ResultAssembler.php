<?php

declare(strict_types=1);

namespace App\Application\User\Assembler;

use App\Application\User\Dto\UpdateCurrencyV1RequestDto;
use App\Application\User\Dto\UpdateCurrencyV1ResultDto;
use App\Domain\Entity\User;

class UpdateCurrencyV1ResultAssembler
{
    public function __construct(private readonly CurrentUserToDtoResultAssembler $currentUserToDtoResultAssembler)
    {
    }

    public function assemble(
        UpdateCurrencyV1RequestDto $dto,
        User $user
    ): UpdateCurrencyV1ResultDto {
        $result = new UpdateCurrencyV1ResultDto();
        $result->user = $this->currentUserToDtoResultAssembler->assemble($user);

        return $result;
    }
}
