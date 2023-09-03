<?php

declare(strict_types=1);


namespace App\Application\User\Dto;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"id", "avatar", "name"}
 * )
 */
class UserResultDto
{
    /**
     * User id
     * @OA\Property(example="f680553f-6b40-407d-a528-5123913be0aa")
     */
    public string $id;

    /**
     * User avatar
     * @var string
     * @OA\Property(example="https://example.com/avatar.jpg")
     */
    public string $avatar;

    /**
     * User name
     * @OA\Property(example="John")
     */
    public string $name;
}
