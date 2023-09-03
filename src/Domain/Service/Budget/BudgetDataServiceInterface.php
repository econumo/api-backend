<?php
declare(strict_types=1);

namespace App\Domain\Service\Budget;

use App\Domain\Entity\ValueObject\Id;
use App\Domain\Service\Budget\Dto\BudgetDataDto;
use DateTimeInterface;

interface BudgetDataServiceInterface
{
    /**
     * @param Id $userId
     * @param DateTimeInterface $dateStart
     * @param DateTimeInterface $dateEnd
     * @return BudgetDataDto[]
     */
    public function getBudgetsData(Id $userId, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): array;
}
