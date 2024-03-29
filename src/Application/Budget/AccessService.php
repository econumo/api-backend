<?php

declare(strict_types=1);

namespace App\Application\Budget;

use App\Application\Budget\Assembler\GrantAccessV1ResultAssembler;
use App\Application\Budget\Dto\GrantAccessV1RequestDto;
use App\Application\Budget\Dto\GrantAccessV1ResultDto;
use App\Application\Budget\Dto\RevokeAccessV1RequestDto;
use App\Application\Budget\Dto\RevokeAccessV1ResultDto;
use App\Application\Budget\Assembler\RevokeAccessV1ResultAssembler;
use App\Application\Exception\AccessDeniedException;
use App\Application\Exception\ValidationException;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Entity\ValueObject\UserRole;
use App\Domain\Exception\RevokeOwnerAccessException;
use App\Domain\Repository\PlanRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\Budget\PlanAccessServiceInterface;
use App\Domain\Service\Budget\PlanServiceInterface;
use App\Application\Budget\Dto\AcceptAccessV1RequestDto;
use App\Application\Budget\Dto\AcceptAccessV1ResultDto;
use App\Application\Budget\Assembler\AcceptAccessV1ResultAssembler;

readonly class AccessService
{
    public function __construct(
        private RevokeAccessV1ResultAssembler $revokeAccessV1ResultAssembler,
        private PlanAccessServiceInterface $planAccessService,
        private PlanServiceInterface $planService,
        private PlanRepositoryInterface $planRepository,
        private GrantAccessV1ResultAssembler $grantAccessV1ResultAssembler,
        private AcceptAccessV1ResultAssembler $acceptAccessV1ResultAssembler,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function revokeAccess(
        RevokeAccessV1RequestDto $dto,
        Id $userId
    ): RevokeAccessV1ResultDto {
        $planId = new Id($dto->planId);
        if (!$this->planAccessService->canManagePlanAccess($userId, $planId)) {
            throw new AccessDeniedException();
        }
        $sharedUserId = new Id($dto->userId);
        try {
            $this->planService->revokeAccess($planId, $sharedUserId);
            $plan = $this->planRepository->get($planId);
            return $this->revokeAccessV1ResultAssembler->assemble($dto, $plan, $userId);
        } catch (RevokeOwnerAccessException $e) {
            throw new ValidationException();
        }
    }

    public function grantAccess(
        GrantAccessV1RequestDto $dto,
        Id $userId
    ): GrantAccessV1ResultDto {
        $planId = new Id($dto->planId);
        if (!$this->planAccessService->canManagePlanAccess($userId, $planId)) {
            throw new AccessDeniedException();
        }
        $sharedUserId = new Id($dto->userId);
        $user = $this->userRepository->get($userId);
        if (!$user->isUserIdConnected($sharedUserId)) {
            throw new AccessDeniedException();
        }
        $role = UserRole::createFromAlias($dto->role);
        $this->planService->grantAccess($planId, $sharedUserId, $role);
        $plan = $this->planRepository->get($planId);
        return $this->grantAccessV1ResultAssembler->assemble($dto, $plan, $userId);
    }

    public function acceptAccess(
        AcceptAccessV1RequestDto $dto,
        Id $userId
    ): AcceptAccessV1ResultDto {
        $planId = new Id($dto->planId);
        $this->planService->acceptAccess($planId, $userId);
        $plan = $this->planRepository->get($planId);
        return $this->acceptAccessV1ResultAssembler->assemble($dto, $plan, $userId);
    }
}
