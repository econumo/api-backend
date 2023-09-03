<?php
declare(strict_types=1);

namespace App\Domain\Service\Budget\Dto;

use DateTimeInterface;

class BudgetDataDto
{
    public function __construct(public readonly DateTimeInterface $dateStart, public readonly DateTimeInterface $dateEnd, public readonly float $totalIncome, public readonly float $totalExpenses, public readonly array $budgets)
    {
    }
}
