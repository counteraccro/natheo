<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour l'entité option_system
 */

namespace App\DataFixtures\Admin;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\OptionSystem;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class OptionSystemFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{

    const OPTION_SYSTEM_FIXTURES_DATA_FILE = 'option_system_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::OPTION_SYSTEM_FIXTURES_DATA_FILE);
        foreach ($data['option_system'] as $data) {
            $optionSystem = new OptionSystem();
            foreach ($data as $key => $value) {
                $this->setData($key, $value, $optionSystem);
            }
            $manager->persist($optionSystem);
        }
        $manager->flush();
    }

    /**
     * Set une valeur pour l'objet OptionSystem
     * @param string $key
     * @param mixed $value
     * @param OptionSystem $optionSystem
     * @return OptionSystem $optionSystem
     */
    private function setData(string $key, mixed $value, OptionSystem $optionSystem): OptionSystem
    {
        $func = 'set' . ucfirst($key);
        $optionSystem->$func($value);
        return $optionSystem;
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
        return 1; // smaller means sooner
    }
}
