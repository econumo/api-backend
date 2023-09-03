<?php

declare(strict_types=1);


namespace App\Domain\Service\Currency;

use App\Domain\Entity\CurrencyRate;
use DateTimeInterface;

interface CurrencyRateServiceInterface
{
    /**
     * @param \DateTimeInterface $dateTime
     * @return array|CurrencyRate[]
     */
    public function getCurrencyRates(DateTimeInterface $dateTime): array;

    /**
     * @return array|CurrencyRate[]
     */
    public function getLatestCurrencyRates(): array;
}
