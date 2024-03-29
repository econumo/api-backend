<?php

declare(strict_types=1);

namespace App\Application\Category\Assembler;

use App\Application\Category\Dto\CategoryResultDto;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Repository\CategoryRepositoryInterface;

class CategoryIdToUserCategoryDtoResultAssembler
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository, private readonly UserCategoryToDtoResultAssembler $categoryToDtoResultAssembler)
    {
    }

    public function assemble(Id $categoryId): CategoryResultDto
    {
        $category = $this->categoryRepository->get($categoryId);
        return $this->categoryToDtoResultAssembler->assemble($category);
    }
}
