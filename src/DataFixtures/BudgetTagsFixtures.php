<?php

namespace App\DataFixtures;


use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BudgetTagsFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public string $tableName = 'budget_tags';

    public function getDependencies(): array
    {
        return [
            BudgetFixtures::class,
            TagFixtures::class
        ];
    }
}
