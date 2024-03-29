<?php

declare(strict_types=1);

namespace App\Application\Budget\Assembler;

use App\Application\Budget\Dto\PlanResultDto;
use App\Application\Budget\Dto\PlanSharedAccessItemResultDto;
use App\Application\User\Assembler\UserToDtoResultAssembler;
use App\Domain\Entity\Plan;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Entity\ValueObject\UserRole;
use App\Domain\Repository\PlanAccessRepositoryInterface;
use App\Domain\Repository\PlanOptionsRepositoryInterface;

readonly class PlanToDtoV1ResultAssembler
{
    public function __construct(
        private PlanAccessRepositoryInterface $planAccessRepository,
        private SharedAccessToResultDtoAssembler $sharedAccessToResultDtoAssembler,
        private UserToDtoResultAssembler $userToDtoResultAssembler,
        private PlanOptionsRepositoryInterface $planOptionsRepository
    ) {
    }

    public function assemble(
        Plan $plan,
        Id $userId
    ): PlanResultDto {
        $item = new PlanResultDto();
        $item->id = $plan->getId()->getValue();
        $item->name = $plan->getName()->getValue();
        try {
            $options = $this->planOptionsRepository->get($plan->getId(), $userId);
            $item->position = $options->getPosition();
        } catch (\Exception) {
            $item->position = 0;
        }
        $item->ownerUserId = $plan->getOwnerUserId()->getValue();
        $item->createdAt = $plan->getCreatedAt()->format('Y-m-d H:i:s');
        $item->updatedAt = $plan->getUpdatedAt()->format('Y-m-d H:i:s');
        $item->sharedAccess = [];
        $access = $this->planAccessRepository->getByPlanId($plan->getId());
        $ownerUserAccess = new PlanSharedAccessItemResultDto();
        $ownerUserAccess->isAccepted = 1;
        $ownerUserAccess->role = UserRole::admin()->getAlias();
        $ownerUserAccess->user = $this->userToDtoResultAssembler->assemble($plan->getOwner());
        $item->sharedAccess[] = $ownerUserAccess;
        foreach ($access as $accessItem) {
            $item->sharedAccess[] = $this->sharedAccessToResultDtoAssembler->assemble($accessItem);
        }

        return $item;
    }
}
