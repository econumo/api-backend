<?php

declare(strict_types=1);

namespace App\Domain\Service\Budget;

use App\Domain\Entity\Budget;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Repository\BudgetRepositoryInterface;
use App\Domain\Repository\TransactionRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\Budget\Dto\BudgetDataDto;
use App\Domain\Service\Budget\Dto\BudgetDataReportDto;
use DatePeriod;
use DateTime;
use DateTimeInterface;

class BudgetDataService implements BudgetDataServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly BudgetRepositoryInterface $budgetRepository,
        private readonly TransactionRepositoryInterface $transactionRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getBudgetsData(Id $userId, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): array
    {
        $result = [];
        $user = $this->userRepository->get($userId);
        $budgets = $this->budgetRepository->getAvailableForUserId($userId);
        $period = $user->getReportPeriod();

        $period = new DatePeriod($dateStart, $period->getDateInterval(), $dateEnd);
        foreach ($period as $reportDateStart) {
            $reportDateEnd = DateTime::createFromInterface($reportDateStart);
            $reportDateEnd->add($period->getDateInterval());
            if ($reportDateEnd > $dateEnd) {
                $reportDateEnd = DateTime::createFromInterface($dateEnd);
            }

            $reports = [];
            foreach ($budgets as $budget) {
                $reportDto = new BudgetDataReportDto(
                    $budget->getId(),
                    $this->getBudgetAmount($budget, $reportDateStart, $reportDateEnd)
                );
                $reports[] = $reportDto;
            }

            $dto = new BudgetDataDto(
                $reportDateStart,
                $reportDateEnd,
                $this->getTotalIncome($userId, $reportDateStart, $reportDateEnd),
                $this->getTotalExpenses($userId, $reportDateStart, $reportDateEnd),
                $reports
            );
            $result[] = $dto;
        }

        return $result;
    }

    private function getTotalIncome(Id $userId, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): float
    {
        return $this->transactionRepository->calculateTotalIncome($userId, $dateStart, $dateEnd);
    }

    private function getTotalExpenses(Id $userId, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): float
    {
        return $this->transactionRepository->calculateTotalExpenses($userId, $dateStart, $dateEnd);
    }

    private function getBudgetAmount(Budget $budget, DateTimeInterface $dateStart, DateTimeInterface $dateEnd): float
    {
        $categoryIds = [];
        foreach ($budget->getCategories() as $category) {
            $categoryIds[] = $category->getId();
        }

        $tagIds = [];
        foreach ($budget->getTags() as $tag) {
            $tagIds[] = $tag->getId();
        }

        return $this->transactionRepository->calculateAmount($categoryIds, $tagIds, $budget->isExcludeTags(), $dateStart, $dateEnd);
    }
}
