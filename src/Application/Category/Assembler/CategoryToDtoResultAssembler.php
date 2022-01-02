<?php

declare(strict_types=1);

namespace App\Application\Category\Assembler;

use App\Application\Category\Dto\CategoryResultDto;
use App\Domain\Entity\Category;

class CategoryToDtoResultAssembler
{
    public function assemble(Category $category): CategoryResultDto
    {
        $item = new CategoryResultDto();
        $item->id = $category->getId()->getValue();
        $item->ownerUserId = $category->getUserId()->getValue();
        $item->name = $category->getName();
        $item->position = $category->getPosition();
        $item->type = $category->getType()->getAlias();
        $item->icon = $category->getIcon()->getValue();
        return $item;
    }
}
