<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixtures pour lier les menus aux pages
 */
namespace App\DataFixtures\Admin\Content\Page;

use App\DataFixtures\AppFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class PageMenuFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const PAGE_MENU_FIXTURES_DATA_FILE = 'content' . DIRECTORY_SEPARATOR . 'page' . DIRECTORY_SEPARATOR . 'page_menu_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::PAGE_MENU_FIXTURES_DATA_FILE);

        foreach ($data['pageMenu'] as $ref => $dataMenu) {
            $page = $this->getReference($dataMenu['page']);
            $menu = $this->getReference($dataMenu['menu']);

            $page->addMenu($menu);
            $manager->persist($page);
        }
        $manager->flush();
    }

    /**
     * @return array|string[]
     */
    public static function getGroups(): array
    {
        return [self::GROUP_PAGE, self::GROUP_MENU, self::GROUP_CONTENT];
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 299;
    }
}
