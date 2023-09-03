<?php

declare(strict_types=1);

namespace App\Application\Budget\Dto;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"id", "spent"}
 * )
 */
class BudgetDataReportResultDto
{
    /**
     * Budget id
     * @OA\Property(example="59e84e2f-7e86-4d55-a650-99c230d0f084")
     */
    public string $id;

    /**
     * Spent
     * @OA\Property(example="15000.00")
     */
    public float $spent;
}
