<?php

declare(strict_types=1);

namespace App\Application\Tag\Tag\Dto;

use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     required={"id", "name"}
 * )
 */
class CreateTagV1RequestDto
{
    /**
     * @SWG\Property(example="123")
     */
    public string $id;

    /**
     * @SWG\Property(example="#shopping")
     */
    public string $name;

    /**
     * @SWG\Property(example="0aaa0450-564e-411e-8018-7003f6dbeb92")
     */
    public ?string $accountId = null;
}
