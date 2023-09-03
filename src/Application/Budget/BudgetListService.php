<?php

declare(strict_types=1);

namespace App\Application\Budget;

use App\Application\Budget\Dto\GetBudgetListV1RequestDto;
use App\Application\Budget\Dto\GetBudgetListV1ResultDto;
use App\Application\Budget\Assembler\GetBudgetListV1ResultAssembler;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Repository\BudgetRepositoryInterface;

class BudgetListService
{
    public function __construct(private readonly GetBudgetListV1ResultAssembler $getBudgetListV1ResultAssembler, private readonly BudgetRepositoryInterface $budgetRepository)
    {
    }

    public function getBudgetList(
        GetBudgetListV1RequestDto $dto,
        Id $userId
    ): GetBudgetListV1ResultDto {
        $budgets = $this->budgetRepository->getAvailableForUserId($userId);
        return $this->getBudgetListV1ResultAssembler->assemble($dto, $budgets);
    }
}
