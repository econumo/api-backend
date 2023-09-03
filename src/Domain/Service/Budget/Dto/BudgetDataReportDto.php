<?php
declare(strict_types=1);

namespace App\Domain\Service\Budget\Dto;

use App\Domain\Entity\ValueObject\Id;
use DateTimeInterface;

class BudgetDataReportDto
{
    public function __construct(public readonly Id $budgetId, public readonly float $spent)
    {
    }
}
