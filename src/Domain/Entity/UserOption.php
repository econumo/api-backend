<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\ValueObject\Id;
use App\Domain\Entity\ValueObject\ReportPeriod;
use App\Domain\Exception\UserOptionException;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class UserOption
{
    /**
     * @var string
     */
    final public const CURRENCY = 'currency';

    /**
     * @var string
     */
    final public const DEFAULT_CURRENCY = 'USD';

    /**
     * @var string
     */
    final public const REPORT_PERIOD = 'report_period';

    /**
     * @var string
     */
    final public const DEFAULT_REPORT_PERIOD = ReportPeriod::MONTHLY;


    /**
     * @var string[]
     */
    final public const OPTIONS = [
        self::CURRENCY,
        self::REPORT_PERIOD
    ];

    private readonly DateTimeImmutable $createdAt;

    private DateTimeInterface $updatedAt;

    public function __construct(
        private Id $id,
        private User $user,
        private string $name,
        private ?string $value,
        DateTimeInterface $createdAt
    ) {
        $this->checkName($name);
        $this->createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
        $this->updatedAt = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function checkName(string $name): void
    {
        if (!in_array($name, self::OPTIONS, true)) {
            throw new UserOptionException();
        }
    }

    public function updateValue(string $value): void
    {
        if ($this->value === $value) {
            return;
        }

        $this->value = $value;
        $this->updated();
    }

    private function updated(): void
    {
        $this->updatedAt = new DateTime();
    }
}
