<?php

declare(strict_types=1);

namespace App\Application\Budget\Assembler;

use App\Application\Budget\Dto\GetBudgetListV1RequestDto;
use App\Application\Budget\Dto\GetBudgetListV1ResultDto;
use App\Domain\Entity\Budget;

class GetBudgetListV1ResultAssembler
{
    public function __construct(private readonly BudgetToResultDtoAssembler $budgetToResultDtoAssembler)
    {
    }

    /**
     * @param Budget[] $budgets
     */
    public function assemble(
        GetBudgetListV1RequestDto $dto,
        array $budgets
    ): GetBudgetListV1ResultDto {
        $result = new GetBudgetListV1ResultDto();
        $result->items = [];
        foreach ($budgets as $budget) {
            $result->items[] = $this->budgetToResultDtoAssembler->assemble($budget);
        }

        return $result;
    }
}
