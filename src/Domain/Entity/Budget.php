<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\ValueObject\BudgetName;
use App\Domain\Entity\ValueObject\Icon;
use App\Domain\Entity\ValueObject\Id;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Budget
{
    private bool $isArchived = false;

    private DateTimeImmutable $createdAt;

    private DateTimeInterface $updatedAt;

    /**
     * @var Collection|Category[]
     */
    private Collection $categories;

    /**
     * @var Collection|Tag[]
     */
    private Collection $tags;

    /**
     * @var Collection|User[]
     */
    private Collection $sharedAccess;

    public function __construct(
        private Id $id,
        private User $user,
        private BudgetName $name,
        private Icon $icon,
        private float $amount,
        DateTimeInterface $createdAt,
        private bool $carryOver = false,
        private bool $carryOverNegative = false,
        private ?DateTimeInterface $carryOverStartDate = null,
        private bool $excludeTags = false,
        array $categories = [],
        array $tags = [],
        array $sharedAccess = [],
    ) {
        $this->createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
        $this->updatedAt = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->sharedAccess = new ArrayCollection();
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getName(): BudgetName
    {
        return $this->name;
    }

    public function getIcon(): Icon
    {
        return $this->icon;
    }

    public function isCarryOver(): bool
    {
        return $this->carryOver;
    }

    public function isCarryOverNegative(): bool
    {
        return $this->carryOverNegative;
    }

    public function getCarryOverStartDate(): ?DateTimeInterface
    {
        return $this->carryOverStartDate;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function isExcludeTags(): bool
    {
        return $this->excludeTags;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return User[]|Collection
     */
    public function getSharedAccess(): Collection
    {
        return $this->sharedAccess;
    }

    /**
     * @return Category[]|Collection
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @return Tag[]|Collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }
}
