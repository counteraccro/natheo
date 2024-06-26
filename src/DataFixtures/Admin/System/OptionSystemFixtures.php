<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour l'entité option_system
 */

namespace App\DataFixtures\Admin\System;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\System\OptionSystem;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class OptionSystemFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{

    const OPTION_SYSTEM_FIXTURES_DATA_FILE = 'system' . DIRECTORY_SEPARATOR . 'option_system_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::OPTION_SYSTEM_FIXTURES_DATA_FILE);
        foreach ($data['option_system'] as $dataOS) {
            $optionSystem = $this->populateEntity($dataOS, new OptionSystem());
            $manager->persist($optionSystem);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [self::GROUP_SYSTEM, self::GROUP_OPTION_SYSTEM, self::GROUP_REGISTERED];
    }

    /**
     * Définition de l'ordre
     * @return int
     */
    public function getOrder(): int
    {
        return 101; // smaller means sooner
    }
}
