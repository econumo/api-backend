<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Currency;
use App\Domain\Entity\ValueObject\CurrencyCode;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\NotFoundException;

interface CurrencyRepositoryInterface
{
    public function getNextIdentity(): Id;

    /**
     * @param Id $id
     * @return Currency
     * @throws NotFoundException
     */
    public function get(Id $id): Currency;

    public function getByCode(CurrencyCode $code): ?Currency;

    /**
     * @return Currency[]
     */
    public function getAll(): array;

    public function getReference(Id $id): Currency;

    /**
     * @param Currency[] $items
     * @return void
     */
    public function save(array $items): void;
}
