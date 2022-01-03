<?php

declare(strict_types=1);

namespace App\Application\Tag\Dto;

use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     required={"ids"}
 * )
 */
class OrderTagListV1RequestDto
{
    /**
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    public array $ids;
}
