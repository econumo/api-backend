<?php
declare(strict_types=1);

namespace App\Application\Account\Dto;

use App\Application\User\Dto\UserResultDto;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={"user", "role"}
 * )
 */
class SharedAccessItemResultDto
{
    /**
     * User
     * @var UserResultDto
     * @OA\Property()
     */
    public UserResultDto $user;

    /**
     * User role
     * @var string
     * @OA\Property(example="admin")
     */
    public string $role;
}
