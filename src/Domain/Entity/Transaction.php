<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\ValueObject\CurrencyCode;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Entity\ValueObject\TransactionType;
use App\Domain\Service\Dto\TransactionDto;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class Transaction
{
    private string $amount;

    private ?string $amountRecipient;

    private DateTimeImmutable $createdAt;

    private DateTimeInterface $updatedAt;

    private DateTimeInterface $spentAt;

    public function __construct(
        private Id $id,
        private User $user,
        private TransactionType $type,
        private Account $account,
        private ?Category $category,
        float $amount,
        DateTimeInterface $transactionDate,
        DateTimeInterface $createdAt,
        private ?Account $accountRecipient,
        ?float $amountRecipient,
        private string $description,
        private ?Payee $payee,
        private ?Tag $tag
    ) {
        $this->amount = (string)$amount;
        $this->amountRecipient = $amountRecipient === null ? null : (string)$amountRecipient;
        $this->createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
        $this->updatedAt = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
        $this->spentAt = DateTime::createFromFormat('Y-m-d H:i:s', $transactionDate->format('Y-m-d H:i:s'));
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getUserId(): Id
    {
        return $this->user->getId();
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getAccountId(): Id
    {
        return $this->account->getId();
    }

    public function getAccountCurrency(): CurrencyCode
    {
        return $this->account->getCurrencyCode();
    }

    public function getAccountRecipientId(): ?Id
    {
        return $this->accountRecipient === null ? null : $this->accountRecipient->getId();
    }

    public function getAmount(): float
    {
        return (float)$this->amount;
    }

    public function getAmountRecipient(): ?float
    {
        return $this->amountRecipient === null ? null : (float)$this->amountRecipient;
    }

    public function getCategoryId(): ?Id
    {
        return $this->category === null ? null : $this->category->getId();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPayeeId(): ?Id
    {
        return $this->payee === null ? null : $this->payee->getId();
    }

    public function getTagId(): ?Id
    {
        return $this->tag === null ? null : $this->tag->getId();
    }

    public function getSpentAt(): DateTimeInterface
    {
        return $this->spentAt;
    }

    public function updateType(TransactionType $transactionType): void
    {
        if (!$this->type->isEqual($transactionType)) {
            $this->type = $transactionType;
            $this->updated();
        }
    }

    public function updateAccount(Account $account): void
    {
        if (!$this->account->getId()->isEqual($account->getId())) {
            $this->account = $account;
            $this->updated();
        }
    }

    public function updateAccountRecipient(?Account $accountRecipient): void
    {
        if ($this->accountRecipient && $accountRecipient && !$this->accountRecipient->getId()->isEqual(
                $accountRecipient->getId()
            )) {
            $this->accountRecipient = $accountRecipient;
            $this->updated();
        } elseif (!$this->accountRecipient && $accountRecipient) {
            $this->accountRecipient = $accountRecipient;
            $this->updated();
        } elseif ($this->accountRecipient && !$accountRecipient) {
            $this->accountRecipient = null;
            $this->updated();
        }
    }

    public function updateAmount(float $amount): void
    {
        if (abs((float)$this->amount - $amount) >= PHP_FLOAT_EPSILON) {
            $this->amount = (string)$amount;
            $this->updated();
        }
    }

    public function updateAmountRecipient(?float $amount): void
    {
        if ($this->accountRecipient === null && $amount !== null) {
            $this->amountRecipient = (string)$amount;
            $this->updated();
        } elseif ($this->accountRecipient !== null && $amount === null) {
            $this->amountRecipient = null;
            $this->updated();
        } elseif ($this->accountRecipient !== null && $amount !== null && abs(
                (float)$this->amountRecipient - $amount
            ) >= PHP_FLOAT_EPSILON) {
            $this->amountRecipient = (string)$amount;
            $this->updated();
        }
    }

    public function updateCategory(?Category $category): void
    {
        if ($this->category && $category && !$this->category->getId()->isEqual($category->getId())) {
            $this->category = $category;
            $this->updated();
        } elseif (!$this->category && $category) {
            $this->category = $category;
            $this->updated();
        } elseif ($this->category && !$category) {
            $this->category = null;
            $this->updated();
        }
    }

    public function updateDescription(?string $description): void
    {
        $newDescription = (string)$description;
        if ($this->description !== $newDescription) {
            $this->description = $newDescription;
            $this->updated();
        }
    }

    public function updatePayee(?Payee $payee): void
    {
        if ($this->payee && $payee && !$this->payee->getId()->isEqual($payee->getId())) {
            $this->payee = $payee;
            $this->updated();
        } elseif (!$this->payee && $payee) {
            $this->payee = $payee;
            $this->updated();
        } elseif ($this->payee && !$payee) {
            $this->payee = null;
            $this->updated();
        }
    }

    public function updateTag(?Tag $tag): void
    {
        if ($this->tag && $tag && !$this->tag->getId()->isEqual($tag->getId())) {
            $this->tag = $tag;
            $this->updated();
        } elseif (!$this->tag && $tag) {
            $this->tag = $tag;
            $this->updated();
        } elseif ($this->tag && !$tag) {
            $this->tag = null;
            $this->updated();
        }
    }

    public function updateDate(DateTimeInterface $dateTime): void
    {
        if ($this->spentAt->format('Y-m-d H:i:s') !== $dateTime->format('Y-m-d H:i:s')) {
            $this->spentAt = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime->format('Y-m-d H:i:s'));
            $this->updated();
        }
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function update(TransactionDto $dto): void
    {
        $this->updateType($dto->type);
        $this->updateAccount($dto->account);
        $this->updateAccountRecipient($dto->accountRecipient);
        $this->updateAmount($dto->amount);
        $this->updateAmountRecipient($dto->amountRecipient);
        $this->updateCategory($dto->category);
        $this->updateDescription($dto->description);
        $this->updatePayee($dto->payee);
        $this->updateTag($dto->tag);
        $this->updateDate($dto->date);
    }

    private function updated(): void
    {
        $this->updatedAt = new DateTime();
    }
}
