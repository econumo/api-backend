<?php

declare(strict_types=1);

namespace App\Application\Budget\Dto;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"items"}
 * )
 */
class GetBudgetDataV1ResultDto
{
    /**
     * @var BudgetDataResultDto[]
     * @OA\Property()
     */
    public array $items = [];
}
