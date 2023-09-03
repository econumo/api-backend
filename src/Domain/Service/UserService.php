<?php

declare(strict_types=1);

namespace App\Domain\Service;

use Throwable;
use App\Domain\Entity\User;
use App\Domain\Entity\UserOption;
use App\Domain\Entity\ValueObject\CurrencyCode;
use App\Domain\Entity\ValueObject\Email;
use App\Domain\Entity\ValueObject\FolderName;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Entity\ValueObject\ReportPeriod;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\UserRegisteredException;
use App\Domain\Factory\ConnectionInviteFactoryInterface;
use App\Domain\Factory\FolderFactoryInterface;
use App\Domain\Factory\UserFactoryInterface;
use App\Domain\Factory\UserOptionFactoryInterface;
use App\Domain\Repository\ConnectionInviteRepositoryInterface;
use App\Domain\Repository\FolderRepositoryInterface;
use App\Domain\Repository\UserOptionRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\Translation\TranslationServiceInterface;

class UserService implements UserServiceInterface
{
    public function __construct(private readonly UserFactoryInterface $userFactory, private readonly UserRepositoryInterface $userRepository, private readonly EventDispatcherInterface $eventDispatcher, private readonly FolderFactoryInterface $folderFactory, private readonly FolderRepositoryInterface $folderRepository, private readonly AntiCorruptionServiceInterface $antiCorruptionService, private readonly TranslationServiceInterface $translator, private readonly ConnectionInviteFactoryInterface $connectionInviteFactory, private readonly ConnectionInviteRepositoryInterface $connectionInviteRepository, private readonly UserOptionFactoryInterface $userOptionFactory, private readonly UserOptionRepositoryInterface $userOptionRepository)
    {
    }

    public function register(Email $email, string $password, string $name): User
    {
        try {
            $this->userRepository->getByEmail($email);
            throw new UserRegisteredException();
        } catch (NotFoundException) {
        }

        $this->antiCorruptionService->beginTransaction();
        try {
            $user = $this->userFactory->create($name, $email, $password);
            $this->userRepository->save([$user]);

            $folder = $this->folderFactory->create($user->getId(), new FolderName($this->translator->trans('account.folder.all_accounts')));
            $this->folderRepository->save([$folder]);

            $connectionInvite = $this->connectionInviteFactory->create($user);
            $this->connectionInviteRepository->save([$connectionInvite]);

            $this->userOptionRepository->save(
                [
                    $this->userOptionFactory->create($user, UserOption::CURRENCY, UserOption::DEFAULT_CURRENCY),
                    $this->userOptionFactory->create($user, UserOption::REPORT_PERIOD, UserOption::DEFAULT_REPORT_PERIOD)
                ]
            );

            $this->antiCorruptionService->commit();
        } catch (Throwable $throwable) {
            $this->antiCorruptionService->rollback();
            throw $throwable;
        }

        $this->eventDispatcher->dispatchAll($user->releaseEvents());
        // do not send first folder creation event
//        $this->eventDispatcher->dispatchAll($folder->releaseEvents());

        return $user;
    }

    public function updateName(Id $userId, string $name): void
    {
        $user = $this->userRepository->get($userId);
        $user->updateName($name);

        $this->userRepository->save([$user]);
    }

    public function updateCurrency(Id $userId, CurrencyCode $currencyCode): void
    {
        $this->antiCorruptionService->beginTransaction();
        try {
            $user = $this->userRepository->get($userId);
            $user->updateCurrency($currencyCode);
            $this->userRepository->save([$user]);
            $this->antiCorruptionService->commit();
        } catch (Throwable $throwable) {
            $this->antiCorruptionService->rollback();
            throw $throwable;
        }
    }

    public function updateReportPeriod(Id $userId, ReportPeriod $reportPeriod): void
    {
        $this->antiCorruptionService->beginTransaction();
        try {
            $user = $this->userRepository->get($userId);
            $user->updateReportPeriod($reportPeriod);
            $this->userRepository->save([$user]);
            $this->antiCorruptionService->commit();
        } catch (Throwable $throwable) {
            $this->antiCorruptionService->rollback();
            throw $throwable;
        }
    }
}
