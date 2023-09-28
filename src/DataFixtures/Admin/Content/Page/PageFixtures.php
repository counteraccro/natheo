<?php

namespace App\DataFixtures\Admin\Content\Page;

use App\DataFixtures\AppFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class PageFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const PAGE_FIXTURES_DATA_FILE = 'page_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::PAGE_FIXTURES_DATA_FILE);

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_PAGE, self::GROUP_CONTENT];
    }

    public function getOrder(): int
    {
        return 204;
    }
}
