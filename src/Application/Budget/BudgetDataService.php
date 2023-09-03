<?php

declare(strict_types=1);

namespace App\Application\Budget;

use App\Application\Budget\Dto\GetBudgetDataV1RequestDto;
use App\Application\Budget\Dto\GetBudgetDataV1ResultDto;
use App\Application\Budget\Assembler\GetBudgetDataV1ResultAssembler;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Service\Budget\BudgetDataServiceInterface;
use DateTimeImmutable;

class BudgetDataService
{
    public function __construct(private readonly GetBudgetDataV1ResultAssembler $getBudgetDataV1ResultAssembler, private readonly BudgetDataServiceInterface $budgetDataService)
    {
    }

    public function getBudgetData(
        GetBudgetDataV1RequestDto $dto,
        Id $userId
    ): GetBudgetDataV1ResultDto {
        $report = $this->budgetDataService->getBudgetsData(
            $userId,
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dto->dateStart),
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dto->dateEnd),
        );
        return $this->getBudgetDataV1ResultAssembler->assemble($dto, $report);
    }
}
