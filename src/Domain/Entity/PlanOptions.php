<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\ValueObject\Id;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class PlanOptions
{
    private DateTimeImmutable $createdAt;

    private DateTimeInterface $updatedAt;

    public function __construct(
        private readonly Plan $plan,
        private readonly User $user,
        private int $position,
        DateTimeInterface $createdAt
    ) {
        $this->createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
        $this->updatedAt = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getPlanId(): Id
    {
        return $this->plan->getId();
    }

    public function getUserId(): Id
    {
        return $this->user->getId();
    }

    public function updatePosition(int $position): void
    {
        if ($this->position !== $position) {
            $this->position = $position;
            $this->updated();
        }
    }

    private function updated(): void
    {
        $this->updatedAt = new DateTime();
    }
}
