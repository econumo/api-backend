<?php

namespace App\DataFixtures;


use App\Domain\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserConnectionFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public string $tableName = 'user_connections';

    public function getDependencies()
    {
        return [User::class];
    }
}
