<?php

namespace App\DataFixtures;


use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BudgetOptionsFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public string $tableName = 'budget_options';

    public function getDependencies(): array
    {
        return [
            BudgetFixtures::class,
            UserFixtures::class
        ];
    }
}
