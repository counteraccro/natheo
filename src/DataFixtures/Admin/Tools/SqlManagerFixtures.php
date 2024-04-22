<?php

namespace App\DataFixtures\Admin\Tools;

use App\DataFixtures\AppFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SqlManagerFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_TOOLS, self::GROUP_SQL_MANAGER];
    }

    public function getOrder(): int
    {
        return 301;
    }
}
