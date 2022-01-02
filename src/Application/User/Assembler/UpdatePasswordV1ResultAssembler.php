<?php

declare(strict_types=1);

namespace App\Application\User\Assembler;

use App\Application\User\Dto\UpdatePasswordV1RequestDto;
use App\Application\User\Dto\UpdatePasswordV1ResultDto;

class UpdatePasswordV1ResultAssembler
{
    public function assemble(
        UpdatePasswordV1RequestDto $dto
    ): UpdatePasswordV1ResultDto {
        return new UpdatePasswordV1ResultDto();
    }
}
