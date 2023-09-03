<?php

namespace App\DataFixtures;


use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BudgetCategoriesFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public string $tableName = 'budget_categories';

    public function getDependencies(): array
    {
        return [
            BudgetFixtures::class,
            CategoryFixtures::class
        ];
    }
}
