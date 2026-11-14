<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixtures pour lier les menus aux pages (clé « menus » de chaque page)
 */
namespace App\DataFixtures\Admin\Content\Page;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Page\Page;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PageMenuFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $pages = $this->loadYamlFolder($this->pathDataFixtures . PageFixtures::PAGE_FIXTURES_FOLDER);
        foreach ($pages as $ref => $file) {
            $page = $this->getReference($ref, Page::class);
            foreach ($file['data']['menus'] ?? [] as $menuRef) {
                $page->addMenu($this->getReference($menuRef, Menu::class));
            }
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
