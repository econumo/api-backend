<?php

declare(strict_types=1);

namespace App\Application\Budget\Assembler;

use App\Application\Budget\Dto\BudgetDataReportResultDto;
use App\Application\Budget\Dto\BudgetDataResultDto;
use App\Application\Budget\Dto\GetBudgetDataV1RequestDto;
use App\Application\Budget\Dto\GetBudgetDataV1ResultDto;
use App\Domain\Service\Budget\Dto\BudgetDataDto;
use App\Domain\Service\Budget\Dto\BudgetDataReportDto;

class GetBudgetDataV1ResultAssembler
{
    /**
     * @param BudgetDataDto[] $reports
     */
    public function assemble(
        GetBudgetDataV1RequestDto $dto,
        array $reports
    ): GetBudgetDataV1ResultDto {
        $result = new GetBudgetDataV1ResultDto();
        $result->items = [];
        foreach ($reports as $report) {
            $dto = new BudgetDataResultDto();
            $dto->dateStart = $report->dateStart->format('Y-m-d H:i:s');
            $dto->dateEnd = $report->dateEnd->format('Y-m-d H:i:s');
            $dto->totalIncome = $report->totalIncome;
            $dto->totalExpense = $report->totalExpenses;
            $dto->budgets = [];
            /** @var BudgetDataReportDto $budget */
            foreach ($report->budgets as $budget) {
                $subDto = new BudgetDataReportResultDto();
                $subDto->id = $budget->budgetId->getValue();
                $subDto->spent = $budget->spent;
                $dto->budgets[] = $subDto;
            }

            $result->items[] = $dto;
        }

        return $result;
    }
}
