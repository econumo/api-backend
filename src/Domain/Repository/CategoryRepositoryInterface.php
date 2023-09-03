<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Category;
use App\Domain\Entity\ValueObject\Id;

interface CategoryRepositoryInterface
{
    public function getNextIdentity(): Id;

    /**
     * @return Category[]
     */
    public function findAvailableForUserId(Id $userId): array;

    /**
     * @return Category[]
     */
    public function findByOwnerId(Id $userId): array;

    public function get(Id $id): Category;

    /**
     * @param Category[] $items
     */
    public function save(array $items): void;

    public function getReference(Id $id): Category;

    public function delete(Category $category): void;
}
