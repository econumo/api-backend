<?php

declare(strict_types=1);

namespace App\Application\Category\Dto;

use App\Application\Category\Dto\CategoryResultDto;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     required={"item"}
 * )
 */
class CreateCategoryV1ResultDto
{
    /**
     * Category
     * @SWG\Property()
     */
    public CategoryResultDto $item;
}