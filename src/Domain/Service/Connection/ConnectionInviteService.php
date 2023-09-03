<?php

declare(strict_types=1);

namespace App\Domain\Service\Connection;

use Throwable;
use App\Domain\Entity\ConnectionInvite;
use App\Domain\Entity\ValueObject\ConnectionCode;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\DomainException;
use App\Domain\Factory\ConnectionInviteFactoryInterface;
use App\Domain\Repository\ConnectionInviteRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\AntiCorruptionServiceInterface;

class ConnectionInviteService implements ConnectionInviteServiceInterface
{
    public function __construct(private readonly ConnectionInviteFactoryInterface $connectionInviteFactory, private readonly ConnectionInviteRepositoryInterface $connectionInviteRepository, private readonly UserRepositoryInterface $userRepository, private readonly AntiCorruptionServiceInterface $antiCorruptionService)
    {
    }

    public function generate(Id $userId): ConnectionInvite
    {
        $connectionInvite = $this->connectionInviteRepository->getByUser($userId);
        if (!$connectionInvite instanceof ConnectionInvite) {
            $connectionInvite = $this->connectionInviteFactory->create($this->userRepository->getReference($userId));
        }

        $connectionInvite->generateNewCode();
        $this->connectionInviteRepository->save([$connectionInvite]);
        return $connectionInvite;
    }

    public function delete(Id $userId): void
    {
        $connectionInvite = $this->connectionInviteRepository->getByUser($userId);
        if (!$connectionInvite instanceof ConnectionInvite) {
            return;
        }

        $connectionInvite->clearCode();
        $this->connectionInviteRepository->save([$connectionInvite]);
    }

    public function accept(Id $userId, ConnectionCode $code): void
    {
        $connectionInvite = $this->connectionInviteRepository->getByCode($code);
        if ($connectionInvite->getUserId()->isEqual($userId)) {
            throw new DomainException('Inviting yourself?');
        }

        $this->antiCorruptionService->beginTransaction();
        try {
            $owner = $this->userRepository->get($connectionInvite->getUserId());
            $guest = $this->userRepository->get($userId);

            $owner->connectUser($guest);
            $guest->connectUser($owner);
            $this->userRepository->save([$owner, $guest]);

            $connectionInvite->clearCode();
            $this->connectionInviteRepository->save([$connectionInvite]);

            $this->antiCorruptionService->commit();
        } catch (Throwable $throwable) {
            $this->antiCorruptionService->rollback();
            throw $throwable;
        }
    }
}
