<?php

declare(strict_types=1);


namespace App\Tests\Helper;


use App\Domain\Entity\ValueObject\Email;
use App\Domain\Repository\UserRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

trait AuthenticationTrait
{
    use ContainerTrait;

    public function amAuthenticatedAsJohn(): void
    {
        $this->amAuthenticatedAsUser('john@snow.test');
    }

    public function amAuthenticatedAsDany(): void
    {
        $this->amAuthenticatedAsUser('dany@targarien.test');
    }

    public function amAuthenticatedAsMargo(): void
    {
        $this->amAuthenticatedAsUser('margo@tirrell.test');
    }

    public function amAuthenticatedAsUser(string $email): void
    {
        /** @var UserRepositoryInterface $userRepository */
        $userRepository = $this->getContainerService(UserRepositoryInterface::class);
        $user = $userRepository->getByEmail(new Email($email));
        /** @var JWTTokenManagerInterface $tokenManager */
        $tokenManager = $this->getContainerService(JWTTokenManagerInterface::class);
        $token = $tokenManager->create($user);
        /** @var \Codeception\Module\REST $rest */
        $rest = $this->getModule('REST');
        $rest->amBearerAuthenticated($token);
    }

}
