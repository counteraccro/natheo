<?php

namespace App\DataFixtures\Admin\Content\Comment;

use App\DataFixtures\AppFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        //$manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_PAGE, self::GROUP_CONTENT, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 291;
    }
}
