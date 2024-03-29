<?php

declare(strict_types=1);

namespace App\Domain\Service\Dto;

use App\Domain\Entity\ValueObject\CurrencyCode;

class CurrencyDto
{
    public CurrencyCode $code;

    public string $symbol;
}
