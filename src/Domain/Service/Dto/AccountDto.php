<?php

declare(strict_types=1);

namespace App\Domain\Service\Dto;

use App\Domain\Entity\ValueObject\Id;

class AccountDto
{
    public Id $userId;

    public float $balance;

    public string $name;

    public Id $currencyId;

    public string $icon;

    public ?Id $folderId = null;
}
