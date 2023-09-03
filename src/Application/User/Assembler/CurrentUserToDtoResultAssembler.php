<?php

declare(strict_types=1);

namespace App\Application\User\Assembler;

use App\Application\User\Dto\CurrentUserResultDto;
use App\Application\User\Dto\OptionResultDto;
use App\Domain\Entity\User;
use App\Domain\Entity\UserOption;

class CurrentUserToDtoResultAssembler
{
    public function assemble(User $user): CurrentUserResultDto
    {
        $dto = new CurrentUserResultDto();
        $dto->id = $user->getId()->getValue();
        $dto->name = $user->getName();
        $dto->email = $user->getUsername();
        $dto->avatar = $user->getAvatarUrl();

        $dto->options = [];
        foreach ($user->getOptions() as $option) {
            $tmp = new OptionResultDto();
            $tmp->name = $option->getName();
            $tmp->value = $option->getValue();
            $dto->options[] = $tmp;
            $options[$option->getName()] = $option->getValue();
        }

        $dto->currency = $options[UserOption::CURRENCY] ?? UserOption::DEFAULT_CURRENCY;
        $dto->reportPeriod = ($options[UserOption::REPORT_PERIOD] ?? UserOption::DEFAULT_REPORT_PERIOD);

        return $dto;
    }
}
