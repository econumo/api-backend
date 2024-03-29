<?php

declare(strict_types=1);

namespace App\Domain\Service\Budget;

use App\Domain\Entity\Category;
use App\Domain\Entity\Envelope;
use App\Domain\Entity\Tag;
use App\Domain\Entity\ValueObject\EnvelopeName;
use App\Domain\Entity\ValueObject\EnvelopeType;
use App\Domain\Entity\ValueObject\Icon;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\EnvelopeIsNotEmptyException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Factory\EnvelopeBudgetFactoryInterface;
use App\Domain\Factory\EnvelopeFactoryInterface;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Domain\Repository\CurrencyRepositoryInterface;
use App\Domain\Repository\EnvelopeBudgetRepositoryInterface;
use App\Domain\Repository\EnvelopeRepositoryInterface;
use App\Domain\Repository\PlanFolderRepositoryInterface;
use App\Domain\Repository\PlanRepositoryInterface;
use App\Domain\Repository\TagRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\AntiCorruptionServiceInterface;
use DateTime;
use DateTimeInterface;
use Throwable;

readonly class EnvelopeService implements EnvelopeServiceInterface
{
    public function __construct(
        private EnvelopeFactoryInterface $envelopeFactory,
        private PlanRepositoryInterface $planRepository,
        private UserRepositoryInterface $userRepository,
        private CurrencyRepositoryInterface $currencyRepository,
        private EnvelopeRepositoryInterface $envelopeRepository,
        private PlanFolderRepositoryInterface $planFolderRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private TagRepositoryInterface $tagRepository,
        private EnvelopeBudgetRepositoryInterface $envelopeBudgetRepository,
        private PlanReportService $planReportService,
        private EnvelopeBudgetFactoryInterface $envelopeBudgetFactory,
        private AntiCorruptionServiceInterface $antiCorruptionService,
    ) {
    }

    public function createConnectedEnvelopesByCategory(Category $category, Id $userId): void
    {
        $plans = $this->planRepository->getAvailableForUserId($userId);
        $user = $this->userRepository->get($userId);
        $userCurrency = $this->currencyRepository->getByCode($user->getCurrency());
        $envelopes = [];
        $isExpense = $category->getType()->isExpense();
        foreach ($plans as $plan) {
            $planEnvelopes = $this->envelopeRepository->getByPlanId($plan->getId());
            $envelopePosition = 0;
            foreach ($planEnvelopes as $planEnvelope) {
                if ($planEnvelope->getType()->isIncome()) {
                    continue;
                }
                if ($planEnvelope->getPosition() > $envelopePosition) {
                    $envelopePosition = $planEnvelope->getPosition();
                }
            }
            $envelopePosition = $envelopePosition === 0 ? 0 : $envelopePosition + 1;

            $folderId = null;
            if ($isExpense) {
                $planFolders = $this->planFolderRepository->getByPlanId($plan->getId());
                if (count($planFolders) > 0) {
                    $folderId = $planFolders[count($planFolders) - 1]->getId();
                }
            }
            $envelopes[] = $this->envelopeFactory->createFromCategory(
                $plan->getId(),
                $category,
                $userCurrency->getId(),
                $envelopePosition,
                $folderId
            );
        }
        $this->planRepository->save($envelopes);
    }

    public function createConnectedEnvelopesByTag(Tag $tag, Id $userId): void
    {
        $plans = $this->planRepository->getAvailableForUserId($userId);
        $user = $this->userRepository->get($userId);
        $userCurrency = $this->currencyRepository->getByCode($user->getCurrency());
        $envelopes = [];
        foreach ($plans as $plan) {
            $planEnvelopes = $this->envelopeRepository->getByPlanId($plan->getId());
            $envelopePosition = 0;
            if (count($planEnvelopes) > 0) {
                $envelopePosition = $planEnvelopes[count($planEnvelopes) - 1]->getPosition() + 1;
            }

            $folderId = null;
            $planFolders = $this->planFolderRepository->getByPlanId($plan->getId());
            if (count($planFolders) > 0) {
                $folderId = $planFolders[count($planFolders) - 1]->getId();
            }
            $envelopes[] = $this->envelopeFactory->createFromTag(
                $plan->getId(),
                $tag,
                $userCurrency->getId(),
                $envelopePosition,
                $folderId
            );
        }
        $this->planRepository->save($envelopes);
    }

    public function createEnvelopesForUser(
        Id $planId,
        Id $userId,
        Id $currencyId,
        int &$envelopePosition,
        Id $folderId
    ): void {
        $envelopes = [];
        $categories = $this->categoryRepository->findByOwnerId($userId);

        foreach ($categories as $category) {
            if ($category->getType()->isIncome()) {
                $envelopes[] = $this->envelopeFactory->createFromCategory(
                    $planId,
                    $category,
                    $currencyId,
                    $envelopePosition++,
                    null
                );
            }
        }
        foreach ($categories as $category) {
            if ($category->getType()->isExpense()) {
                $envelopes[] = $this->envelopeFactory->createFromCategory(
                    $planId,
                    $category,
                    $currencyId,
                    $envelopePosition++,
                    $folderId
                );
            }
        }
        $tags = $this->tagRepository->findByOwnerId($userId);
        foreach ($tags as $tag) {
            $envelopes[] = $this->envelopeFactory->createFromTag(
                $planId,
                $tag,
                $currencyId,
                $envelopePosition++,
                $folderId
            );
        }
        $this->envelopeRepository->save($envelopes);
    }

    public function getEnvelopesBudgets(Id $planId, DateTimeInterface $date): array
    {
        $items = $this->envelopeBudgetRepository->getByPlanIdAndPeriod($planId, $date);
        $result = [];
        foreach ($items as $item) {
            $result[$item->getEnvelope()->getId()->getValue()] = $item;
        }

        return $result;
    }

    public function getEnvelopesAvailable(Id $planId, DateTimeInterface $date): array
    {
        $plan = $this->planRepository->get($planId);
        $planStartDate = $plan->getStartDate();
        $envelopes = $this->envelopeRepository->getByPlanId($plan->getId());
        $result = [];
        if ($planStartDate > $date) {
            return $result;
        }

        $categoriesReport = $this->planReportService->getCategoriesReport($planId, $planStartDate, $date);
        $tagsReport = $this->planReportService->getTagsReport($planId, $planStartDate, $date);
        $budget = $this->envelopeBudgetRepository->getSumByPlanIdAndPeriod($planId, $date);

        foreach ($envelopes as $envelope) {
            $result[$envelope->getId()->getValue()] = null;
            foreach ($envelope->getCategories() as $category) {
                if (isset($categoriesReport[$category->getId()->getValue()])) {
                    $result[$envelope->getId()->getValue()] += $categoriesReport[$category->getId()->getValue()];
                }
            }
            foreach ($envelope->getTags() as $tag) {
                if (isset($tagsReport[$tag->getId()->getValue()])) {
                    $result[$envelope->getId()->getValue()] += $tagsReport[$tag->getId()->getValue()];
                }
            }
        }
        foreach ($result as $envelopeId => $amount) {
            if (array_key_exists($envelopeId, $budget)) {
                $result[$envelopeId] = $budget[$envelopeId]['budget'] - $amount;
            }
        }

        return $result;
    }

    public function updateEnvelopeBudget(Id $envelopeId, DateTimeInterface $period, float $amount): void
    {
        $envelope = $this->envelopeRepository->get($envelopeId);
        $plan = $envelope->getPlan();
        $updatedPeriod = DateTime::createFromFormat('Y-m-d H:i:s', $period->format('Y-m-01 00:00:00'));
        $this->antiCorruptionService->beginTransaction(__METHOD__);
        try {
            if ($plan->getStartDate() > $updatedPeriod) {
                $plan->updateStartDate($updatedPeriod);
                $this->planRepository->save([$plan]);
            }
            try {
                $envelopeBudget = $this->envelopeBudgetRepository->getByEnvelopeIdAndPeriod(
                    $envelopeId,
                    $updatedPeriod
                );
            } catch (NotFoundException) {
                $envelopeBudget = $this->envelopeBudgetFactory->create($envelopeId, $updatedPeriod, $amount);
            }
            $envelopeBudget->updateAmount($amount);
            $this->envelopeBudgetRepository->save([$envelopeBudget]);
            $this->antiCorruptionService->commit(__METHOD__);
        } catch (Throwable $e) {
            $this->antiCorruptionService->rollback(__METHOD__);
            throw $e;
        }
    }

    public function transferEnvelopeBudget(
        Id $fromEnvelopeId,
        Id $toEnvelopeId,
        DateTimeInterface $period,
        float $amount
    ): void {
        $fromEnvelope = $this->envelopeRepository->get($fromEnvelopeId);
        $toEnvelope = $this->envelopeRepository->get($toEnvelopeId);
        $plan = $fromEnvelope->getPlan();
        if (!$fromEnvelope->getPlan()->getId()->isEqual($toEnvelope->getPlan()->getId())) {
            throw new DomainException();
        }

        $updatedPeriod = DateTime::createFromFormat('Y-m-d H:i:s', $period->format('Y-m-01 00:00:00'));
        $this->antiCorruptionService->beginTransaction(__METHOD__);
        try {
            if ($plan->getStartDate() > $updatedPeriod) {
                $plan->updateStartDate($updatedPeriod);
                $this->planRepository->save([$plan]);
            }
            try {
                $fromEnvelopeBudget = $this->envelopeBudgetRepository->getByEnvelopeIdAndPeriod(
                    $fromEnvelopeId,
                    $updatedPeriod
                );
            } catch (NotFoundException) {
                $fromEnvelopeBudget = $this->envelopeBudgetFactory->create($fromEnvelopeId, $updatedPeriod, 0);
            }
            try {
                $toEnvelopeBudget = $this->envelopeBudgetRepository->getByEnvelopeIdAndPeriod(
                    $toEnvelopeId,
                    $updatedPeriod
                );
            } catch (NotFoundException) {
                $toEnvelopeBudget = $this->envelopeBudgetFactory->create($toEnvelopeId, $updatedPeriod, 0);
            }

            $fromEnvelopeBudget->updateAmount($fromEnvelopeBudget->getAmount() - $amount);
            $toEnvelopeBudget->updateAmount($toEnvelopeBudget->getAmount() + $amount);
            $this->envelopeBudgetRepository->save([$fromEnvelopeBudget, $toEnvelopeBudget]);
            $this->antiCorruptionService->commit(__METHOD__);
        } catch (Throwable $e) {
            $this->antiCorruptionService->rollback(__METHOD__);
            throw $e;
        }
    }

    public function orderEnvelopes(Id $planId, array $changes): void
    {
        $folders = $this->planFolderRepository->getByPlanId($planId);
        $envelopes = $this->envelopeRepository->getByPlanId($planId);
        $changed = [];
        foreach ($envelopes as $envelope) {
            foreach ($changes as $change) {
                if ($envelope->getId()->isEqual(new Id ($change->id))) {
                    $envelope->updatePosition($change->position);
                    if ($change->folderId !== null) {
                        foreach ($folders as $folder) {
                            if ($change->folderId && $folder->getId()->isEqual(new Id($change->folderId))) {
                                $envelope->updateFolder($folder);
                                break;
                            }
                        }
                    }
                    $changed[] = $envelope;
                    break;
                }
            }
        }

        if ($changed === []) {
            return;
        }
        $this->envelopeRepository->save($changed);
    }

    public function deleteEnvelope(Id $envelopeId): void
    {
        $envelope = $this->envelopeRepository->get($envelopeId);
        if (count($envelope->getCategories()) || count($envelope->getTags())) {
            throw new EnvelopeIsNotEmptyException();
        }

        $this->envelopeRepository->delete($envelope);
    }

    public function updateEnvelope(
        Id $envelopeId,
        EnvelopeName $name,
        Icon $icon,
        Id $currencyId,
        array $categoriesIds,
        array $tagsIds
    ): void {
        try {
            $this->antiCorruptionService->beginTransaction(__METHOD__);
            $envelope = $this->envelopeRepository->get($envelopeId);

            $envelope->updateName($name);
            $envelope->updateIcon($icon);
            $currency = $this->currencyRepository->getReference($currencyId);
            $envelope->updateCurrency($currency);

            foreach ($envelope->getCategories() as $category) {
                $isCategoryExist = false;
                foreach ($categoriesIds as $categoryId) {
                    if ($category->getId()->isEqual($categoryId)) {
                        $isCategoryExist = true;
                        break;
                    }
                }
                if (!$isCategoryExist) {
                    throw new DomainException('Category was removed');
                }
            }
            foreach ($envelope->getTags() as $tag) {
                $isTagExist = false;
                foreach ($tagsIds as $tagId) {
                    if ($tag->getId()->isEqual($tagId)) {
                        $isTagExist = true;
                        break;
                    }
                }
                if (!$isTagExist) {
                    throw new DomainException('Tag was removed');
                }
            }

            $availableCategories = [];
            $availableTags = [];
            $removeCategories = [];
            $removeTags = [];
            foreach ($this->envelopeRepository->getByPlanId($envelope->getPlan()->getId()) as $item) {
                foreach ($item->getCategories() as $category) {
                    $availableCategories[$category->getId()->getValue()] = $category;
                    foreach ($categoriesIds as $categoryId) {
                        if ($category->getId()->isEqual($categoryId) && !$item->getId()->isEqual($envelopeId)) {
                            $removeCategories[$category->getId()->getValue()] = [$item, $category];
                            break;
                        }
                    }
                }
                foreach ($item->getTags() as $tag) {
                    $availableTags[$tag->getId()->getValue()] = $tag;
                    foreach ($tagsIds as $tagId) {
                        if ($tag->getId()->isEqual($tagId) && !$item->getId()->isEqual($envelopeId)) {
                            $removeTags[$tag->getId()->getValue()] = [$item, $tag];
                            break;
                        }
                    }
                }
            }
            foreach ($envelope->getCategories() as $category) {
                if (isset($availableCategories[$category->getId()->getValue()])) {
                    unset($availableCategories[$category->getId()->getValue()]);
                }
            }
            foreach ($envelope->getTags() as $tag) {
                if (isset($availableTags[$tag->getId()->getValue()])) {
                    unset($availableTags[$tag->getId()->getValue()]);
                }
            }

            foreach ($categoriesIds as $categoryId) {
                foreach ($availableCategories as $availableCategory) {
                    if ($availableCategory->getId()->isEqual($categoryId)) {
                        $envelope->addCategory($this->categoryRepository->getReference($categoryId));
                        break;
                    }
                }
            }

            foreach ($tagsIds as $tagId) {
                foreach ($availableTags as $availableTag) {
                    if ($availableTag->getId()->isEqual($tagId)) {
                        $envelope->addTag($this->tagRepository->getReference($tagId));
                        break;
                    }
                }
            }

            /** @var Envelope[] $envelopesToDelete */
            $envelopesToDelete = [];
            $affectedEnvelopes = [$envelope->getId()->getValue() => $envelope];
            foreach ($removeCategories as $item) {
                if ($item[0]->isCategoryConnected()) {
                    $envelopesToDelete[$item[0]->getId()->getValue()] = $item[0];
                }
                $item[0]->removeCategory($item[1]);
                $affectedEnvelopes[$envelope->getId()->getValue()] = $item[0];
            }
            foreach ($removeTags as $item) {
                if ($item[0]->isTagConnected()) {
                    $envelopesToDelete[$item[0]->getId()->getValue()] = $item[0];
                }
                $item[0]->removeTag($item[1]);
                $affectedEnvelopes[$envelope->getId()->getValue()] = $item[0];
            }

            $this->envelopeRepository->save($affectedEnvelopes);
            foreach ($envelopesToDelete as $item) {
                $this->envelopeRepository->delete($item);
            }
            $this->antiCorruptionService->commit(__METHOD__);
        } catch (Throwable $e) {
            $this->antiCorruptionService->rollback(__METHOD__);
            throw $e;
        }
    }

    public function createEnvelope(
        Id $planId,
        EnvelopeType $type,
        EnvelopeName $name,
        Icon $icon,
        Id $currencyId,
        array $categoriesIds,
        array $tagsIds,
        ?Id $folderId
    ): Id {
        $this->antiCorruptionService->beginTransaction(__METHOD__);
        try {
            $envelope = $this->envelopeFactory->create(
                $planId,
                $type,
                $currencyId,
                0,
                $folderId,
                $name,
                $icon
            );
            $this->envelopeRepository->save([$envelope]);

            $availableCategories = [];
            $availableTags = [];
            $removeCategories = [];
            $removeTags = [];
            foreach ($this->envelopeRepository->getByPlanId($envelope->getPlan()->getId()) as $item) {
                foreach ($item->getCategories() as $category) {
                    $availableCategories[$category->getId()->getValue()] = $category;
                    foreach ($categoriesIds as $categoryId) {
                        if ($category->getId()->isEqual($categoryId)) {
                            $removeCategories[$category->getId()->getValue()] = [$item, $category];
                            break;
                        }
                    }
                }
                foreach ($item->getTags() as $tag) {
                    $availableTags[$tag->getId()->getValue()] = $tag;
                    foreach ($tagsIds as $tagId) {
                        if ($tag->getId()->isEqual($tagId)) {
                            $removeTags[$tag->getId()->getValue()] = [$item, $tag];
                            break;
                        }
                    }
                }
            }

            foreach ($categoriesIds as $categoryId) {
                foreach ($availableCategories as $availableCategory) {
                    if ($availableCategory->getId()->isEqual($categoryId)) {
                        $envelope->addCategory($this->categoryRepository->getReference($categoryId));
                        break;
                    }
                }
            }

            foreach ($tagsIds as $tagId) {
                foreach ($availableTags as $availableTag) {
                    if ($availableTag->getId()->isEqual($tagId)) {
                        $envelope->addTag($this->tagRepository->getReference($tagId));
                        break;
                    }
                }
            }

            /** @var Envelope[] $envelopesToDelete */
            $envelopesToDelete = [];
            $affectedEnvelopes = [$envelope->getId()->getValue() => $envelope];
            foreach ($removeCategories as $item) {
                if ($item[0]->isCategoryConnected()) {
                    $envelopesToDelete[$item[0]->getId()->getValue()] = $item[0];
                }
                $item[0]->removeCategory($item[1]);
                $affectedEnvelopes[$envelope->getId()->getValue()] = $item[0];
            }
            foreach ($removeTags as $item) {
                if ($item[0]->isTagConnected()) {
                    $envelopesToDelete[$item[0]->getId()->getValue()] = $item[0];
                }
                $item[0]->removeTag($item[1]);
                $affectedEnvelopes[$envelope->getId()->getValue()] = $item[0];
            }

            $this->envelopeRepository->save($affectedEnvelopes);
            foreach ($envelopesToDelete as $item) {
                $this->envelopeRepository->delete($item);
            }
            $this->antiCorruptionService->commit(__METHOD__);

            return $envelope->getId();
        } catch (Throwable $e) {
            $this->antiCorruptionService->rollback(__METHOD__);
            throw $e;
        }
    }

    public function deleteConnectedEnvelopeByCategory(Id $categoryId): void
    {
        $this->antiCorruptionService->beginTransaction(__METHOD__);
        $envelopes = $this->envelopeRepository->getByCategoryId($categoryId);
        try {
            foreach ($envelopes as $envelope) {
                if ($envelope->isCategoryConnected()) {
                    $this->envelopeRepository->delete($envelope);
                }
            }
            $this->antiCorruptionService->commit(__METHOD__);
        } catch (Throwable $e) {
            $this->antiCorruptionService->rollback(__METHOD__);
            throw $e;
        }
    }

    public function deleteConnectedEnvelopeByTag(Id $tagId): void
    {
        $this->antiCorruptionService->beginTransaction(__METHOD__);
        $envelopes = $this->envelopeRepository->getByTagId($tagId);
        try {
            foreach ($envelopes as $envelope) {
                if ($envelope->isTagConnected()) {
                    $this->envelopeRepository->delete($envelope);
                }
            }
            $this->antiCorruptionService->commit(__METHOD__);
        } catch (Throwable $e) {
            $this->antiCorruptionService->rollback(__METHOD__);
            throw $e;
        }
    }

    public function copyEnvelopePlan(Id $planId, DateTimeInterface $fromPeriod, DateTimeInterface $toPeriod): void
    {
        $this->antiCorruptionService->beginTransaction(__METHOD__);
        try {
            $toEnvelopeBudgets = $this->envelopeBudgetRepository->getByPlanIdAndPeriod($planId, $toPeriod);
            foreach ($toEnvelopeBudgets as $toEnvelopeBudget) {
                $this->envelopeBudgetRepository->delete($toEnvelopeBudget);
            }

            $envelopeBudgets = [];
            $fromEnvelopeBudgets = $this->envelopeBudgetRepository->getByPlanIdAndPeriod($planId, $fromPeriod);
            foreach ($fromEnvelopeBudgets as $fromEnvelopeBudget) {
                try {
                    $toEnvelopeBudget = $this->envelopeBudgetRepository->getByEnvelopeIdAndPeriod(
                        $fromEnvelopeBudget->getEnvelope()->getId(),
                        $toPeriod
                    );
                } catch (NotFoundException) {
                    $toEnvelopeBudget = $this->envelopeBudgetFactory->create(
                        $fromEnvelopeBudget->getEnvelope()->getId(),
                        $toPeriod,
                        $fromEnvelopeBudget->getAmount()
                    );
                }
                $envelopeBudgets[$toEnvelopeBudget->getId()->getValue()] = $toEnvelopeBudget;
            }
            $this->envelopeBudgetRepository->save($envelopeBudgets);
            $this->antiCorruptionService->commit(__METHOD__);
        } catch (Throwable $e) {
            $this->antiCorruptionService->rollback(__METHOD__);
            throw $e;
        }
    }
}
