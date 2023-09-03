<?php

declare(strict_types=1);

namespace App\Application\Budget\Dto;

use App\Application\Category\Dto\CategoryResultDto;
use App\Application\Tag\Dto\TagResultDto;
use App\Application\User\Dto\UserResultDto;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"id", "name"}
 * )
 */
class BudgetResultDto
{
    /**
     * Id
     * @var string
     * @OA\Property(example="a5e2eee2-56aa-43c6-a827-ca155683ea8d")
     */
    public string $id;

    /**
     * Name
     * @var string
     * @OA\Property(example="Groceries")
     */
    public string $name;

    /**
     * Icon
     * @var string
     * @OA\Property(example="food")
     */
    public string $icon;

    /**
     * Carry over
     * @var int
     * @OA\Property(example="0")
     */
    public int $carryOver = 0;

    /**
     * Carry over negative
     * @var int
     * @OA\Property(example="0")
     */
    public int $carryOverNegative = 0;

    /**
     * Carry over start date
     * @var string
     * @OA\Property(example="2022-09-09 00:00:00")
     */
    public string $carryOverStartDate = '';

    /**
     * Budget amount
     * @var float
     * @OA\Property(example="1000.00")
     */
    public float $amount;

    /**
     * Position
     * @var int
     * @OA\Property(example="1")
     */
    public int $position;

    /**
     * Owner
     * @OA\Property()
     */
    public UserResultDto $owner;

    /**
     * Budget access
     * @var UserResultDto[]
     * @OA\Property()
     */
    public array $sharedAccess = [];

    /**
     * Categories
     * @var CategoryResultDto[]
     * @OA\Property()
     */
    public array $categories = [];

    /**
     * Tags
     * @var TagResultDto[]
     * @OA\Property()
     */
    public array $tags = [];

    /**
     * Exclude tags
     * @var int
     * @OA\Property(example="0")
     */
    public int $excludeTags = 0;
}
