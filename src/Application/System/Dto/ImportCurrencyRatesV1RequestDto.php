<?php

declare(strict_types=1);

namespace App\Application\System\Dto;

use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @OA\Schema(
 *     required={"items"}
 * )
 */
class ImportCurrencyRatesV1RequestDto
{
    /**
     * @OA\Property(example="1694887204")
     */
    public string $timestamp;

    /**
     * @OA\Property(example="USD")
     */
    public string $base;

    /**
     * @var CurrencyRateRequestDto[]
     * @OA\Property(type="array", @OA\Items(type="object", ref=@Model(type=\App\Application\System\Dto\CurrencyRateRequestDto::class)))
     */
    public array $items = [];
}
