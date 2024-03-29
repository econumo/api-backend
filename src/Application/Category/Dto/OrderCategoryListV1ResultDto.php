<?php

declare(strict_types=1);

namespace App\Application\Category\Dto;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"items"}
 * )
 */
class OrderCategoryListV1ResultDto
{
    /**
     * @var CategoryResultDto[]
     * @OA\Property()
     */
    public array $items = [];
}
