<?php

declare(strict_types=1);

namespace App\Application\Budget\Dto;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"dateStart", "dateEnd"}
 * )
 */
class GetBudgetDataV1RequestDto
{
    /**
     * @OA\Property(example="2022-02-02 00:00:00")
     */
    public string $dateStart;

    /**
     * @OA\Property(example="2023-02-02 00:00:00")
     */
    public string $dateEnd;
}
