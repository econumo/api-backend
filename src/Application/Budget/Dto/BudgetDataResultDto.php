<?php

declare(strict_types=1);

namespace App\Application\Budget\Dto;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"dateStart", "dateEnd", "totalIncome", "totalExpense", "budgets"}
 * )
 */
class BudgetDataResultDto
{
    /**
     * Date start
     * @OA\Property(example="2022-02-02 00:00:00")
     */
    public string $dateStart;

    /**
     * Date end
     * @OA\Property(example="2023-02-02 00:00:00")
     */
    public string $dateEnd;

    /**
     * Total income
     * @OA\Property(example="150000.0")
     */
    public float $totalIncome;

    /**
     * Total expense
     * @OA\Property(example="100000.0")
     */
    public float $totalExpense;

    /**
     * @var BudgetDataReportResultDto[]
     * @OA\Property()
     */
    public array $budgets = [];
}
