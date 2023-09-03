<?php

declare(strict_types=1);


namespace App\Domain\Service\Currency;

use App\Domain\Entity\CurrencyRate;
use App\Domain\Repository\CurrencyRateRepositoryInterface;
use DateTimeInterface;

class CurrencyRateService implements CurrencyRateServiceInterface
{
    public function __construct(private readonly CurrencyRateRepositoryInterface $currencyRateRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyRates(DateTimeInterface $dateTime): array
    {
        return $this->currencyRateRepository->getAll($dateTime);
    }

    /**
     * @inheritDoc
     */
    public function getLatestCurrencyRates(): array
    {
        return $this->currencyRateRepository->getAll();
    }

    public function getChanged(DateTimeInterface $lastUpdate): array
    {
        $currencyRates = $this->getLatestCurrencyRates();
        $result = [];
        foreach ($currencyRates as $currencyRate) {
            if ($currencyRate->getPublishedAt() > $lastUpdate) {
                $result[] = $currencyRate;
            }
        }

        return $result;
    }
}
