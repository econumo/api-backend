<?php

declare(strict_types=1);

namespace App\Application\Budget\Assembler;

use App\Application\Budget\Dto\BudgetResultDto;
use App\Application\Category\Assembler\CategoryToDtoResultAssembler;
use App\Application\Tag\Assembler\TagToDtoResultAssembler;
use App\Application\User\Assembler\UserToDtoResultAssembler;
use App\Domain\Entity\Budget;

class BudgetToResultDtoAssembler
{
    public function __construct(private readonly UserToDtoResultAssembler $userToDtoResultAssembler, private readonly CategoryToDtoResultAssembler $categoryToDtoResultAssembler, private readonly TagToDtoResultAssembler $tagToDtoResultAssembler)
    {
    }

    public function assemble(Budget $budget): BudgetResultDto
    {
        $dto = new BudgetResultDto();
        $dto->id = $budget->getId()->getValue();
        $dto->name = $budget->getName()->getValue();
        $dto->icon = $budget->getIcon()->getValue();
        $dto->carryOver = (int)$budget->isCarryOver();
        $dto->carryOverNegative = (int)$budget->isCarryOverNegative();
        $dto->carryOverStartDate = ($budget->getCarryOverStartDate() === null ? '' : $budget->getCarryOverStartDate()->format('Y-m-d H:i:s'));
        $dto->amount = $budget->getAmount();

        $dto->position = 0;
        $dto->owner = $this->userToDtoResultAssembler->assemble($budget->getUser());
        $dto->sharedAccess = [];
        foreach ($budget->getSharedAccess() as $sharedAccess) {
            $dto->sharedAccess[] = $this->userToDtoResultAssembler->assemble($sharedAccess);
        }

        $dto->categories = [];
        foreach ($budget->getCategories() as $category) {
            $dto->categories[] = $this->categoryToDtoResultAssembler->assemble($category);
        }

        $dto->tags = [];
        foreach ($budget->getTags() as $tag) {
            $dto->tags[] = $this->tagToDtoResultAssembler->assemble($tag);
        }

        $dto->excludeTags = (int)$budget->isExcludeTags();

        return $dto;
    }
}
