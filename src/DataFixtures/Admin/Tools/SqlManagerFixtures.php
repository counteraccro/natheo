<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Fixture des sqlManager
 */
namespace App\DataFixtures\Admin\Tools;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\System\User;
use App\Entity\Admin\Tools\SqlManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class SqlManagerFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const SQL_MANAGER_FIXTURES_DATA_FILE = 'tools' . DIRECTORY_SEPARATOR . 'sql_manager_fixtures_data.yaml';

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::SQL_MANAGER_FIXTURES_DATA_FILE);

        foreach ($data['sql_manager'] as $ref => $sqlManagerData) {
            $sqlManager = new SqlManager();
            foreach ($sqlManagerData as $key => $value) {
                switch ($key) {
                    case "user" :
                        $sqlManager->setUser($this->getReference($value, User::class));
                        break;
                    default:
                        $this->setData($key, $value, $sqlManager);
                }
            }
            $manager->persist($sqlManager);
            $this->addReference($ref, $sqlManager);
        }
        $manager->flush();
    }

    /**
     * @return array|string[]
     */
    public static function getGroups(): array
    {
        return [self::GROUP_TOOLS, self::GROUP_SQL_MANAGER];
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 301;
    }
}
