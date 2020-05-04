<?php
declare(strict_types=1);

namespace App\Application\User;

use App\Application\User\Assembler\LoginDisplayAssembler;
use App\Application\User\Dto\LoginDisplayDto;
use App\Domain\Entity\User\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserService
{
    /**
     * @var LoginDisplayAssembler
     */
    private $loginDisplayAssembler;
    /**
     * @var JWTTokenManagerInterface
     */
    private $authToken;

    public function __construct(
        AuthTokenInterface $authToken,
        LoginDisplayAssembler $loginDisplayAssembler
    ) {
        $this->loginDisplayAssembler = $loginDisplayAssembler;
        $this->authToken = $authToken;
    }

    public function login(User $user): LoginDisplayDto
    {
        $token = $this->authToken->create($user);

        return $this->loginDisplayAssembler->assemble($token);
    }
}
