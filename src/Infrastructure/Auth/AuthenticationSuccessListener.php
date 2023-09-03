<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\User\Assembler\CurrentUserToDtoResultAssembler;
use App\Domain\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    public function __construct(private readonly CurrentUserToDtoResultAssembler $currentUserToDtoResultAssembler)
    {
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $data['user'] = $this->currentUserToDtoResultAssembler->assemble($user);
        $event->setData($data);
    }
}
