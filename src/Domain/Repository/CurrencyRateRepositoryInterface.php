<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use DateTimeInterface;
use App\Domain\Entity\CurrencyRate;
use App\Domain\Entity\ValueObject\Id;

interface CurrencyRateRepositoryInterface
{
    public function getNextIdentity(): Id;

    public function get(Id $currencyId, DateTimeInterface $date): CurrencyRate;

    public function getLatest(Id $currencyId, ?DateTimeInterface $date = null): CurrencyRate;

    /**
     * @return CurrencyRate[]
     */
    public function getAll(?DateTimeInterface $date = null): array;

    /**
     * @param CurrencyRate[] $items
     */
    public function save(array $items): void;
}
