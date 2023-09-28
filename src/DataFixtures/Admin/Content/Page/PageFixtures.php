<?php

namespace App\DataFixtures\Admin\Content\Page;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Page\Page;
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

        foreach($data['pages'] as $ref => $data) {

            $page = new Page();
            foreach ($data as $key => $value) {
                switch ($key) {
                    case "user" :
                        $page->setUser($this->getReference($value));
                        break;
                    default:
                        $this->setData($key, $value, $page);
                }
            }

            $manager->persist($page);
            $this->addReference($ref, $page);
        }

        $manager->flush();
    }

    /**
     * Set une valeur pour l'objet Page
     * @param string $key
     * @param mixed $value
     * @param mixed $entity
     * @return void
     */
    private function setData(string $key, mixed $value, mixed $entity): void
    {
        $func = 'set' . ucfirst($key);
        $entity->$func($value);
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
