<?php

namespace App\DataFixtures;


use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BudgetAccessFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public string $tableName = 'budget_access';

    public function getDependencies(): array
    {
        return [
            BudgetFixtures::class,
            UserFixtures::class
        ];
    }
}
