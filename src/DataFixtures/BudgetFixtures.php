<?php

namespace App\DataFixtures;


use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BudgetFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public string $tableName = 'budgets';

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
            TagFixtures::class
        ];
    }
}
