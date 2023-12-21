<?php

namespace App\DataFixtures\Admin\Content\Faq;

use App\DataFixtures\AppFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

;

class FaqFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const FAQ_FIXTURES_DATA_FILE = 'content' . DIRECTORY_SEPARATOR . 'faq' . DIRECTORY_SEPARATOR .
    'faq_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::FAQ_FIXTURES_DATA_FILE);

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_FAQ, self::GROUP_CONTENT];
    }

    public function getOrder()
    {
        return 204;
    }
}
