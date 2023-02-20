<?php

declare(strict_types=1);

namespace App\Domain\Entity\ValueObject;

use App\Domain\Exception\DomainException;
use App\Domain\Traits\ValueObjectTrait;
use DateInterval;
use JsonSerializable;

class ReportPeriod implements ValueObjectInterface, JsonSerializable
{
    use ValueObjectTrait;

    /**
     * @var string
     */
    public const MONTHLY = 'monthly';

    /**
     * @var array<string, int>
     */
    private const OPTIONS = [
        self::MONTHLY
    ];

    private const DATE_INTERVAL_MAPPING = [
        self::MONTHLY => '1 month'
    ];

    public static function validate($value): void
    {
        if (!in_array($value, self::OPTIONS, true)) {
            throw new DomainException('ReportPeriod is incorrect');
        }
    }

    public function getDateInterval(): DateInterval
    {
        return DateInterval::createFromDateString(self::DATE_INTERVAL_MAPPING[$this->value]);
    }
}