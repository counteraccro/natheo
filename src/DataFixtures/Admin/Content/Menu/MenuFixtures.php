<?php

namespace App\DataFixtures\Admin\Content\Menu;

use App\DataFixtures\AppFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MenuFixtures extends AppFixtures  implements FixtureGroupInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }

    /**
     * @return array|string[]
     */
    public static function getGroups(): array
    {
        return [self::GROUP_MENU, self::GROUP_CONTENT];
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 299;
    }
}
