<?php

declare(strict_types=1);

namespace App\Infrastructure\Datetime;

use DateTimeInterface;
use App\Domain\Service\DatetimeServiceInterface;
use DateTimeImmutable;

class DatetimeService implements DatetimeServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getCurrentDatetime(): DateTimeInterface
    {
        return new DateTimeImmutable();
    }
}
