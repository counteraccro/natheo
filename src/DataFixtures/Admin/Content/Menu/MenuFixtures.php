<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixtures pour la génération des menus
 */

namespace App\DataFixtures\Admin\Content\Menu;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Menu\MenuElementTranslation;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class MenuFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const MENU_FIXTURES_DATA_FILE = 'content' . DIRECTORY_SEPARATOR . 'menu' . DIRECTORY_SEPARATOR . 'menu_fixtures_data.yaml';

    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::MENU_FIXTURES_DATA_FILE);

        foreach ($data['menu'] as $ref => $dataMenu) {
            $menu = new Menu();

            foreach ($dataMenu as $key => $value) {
                switch ($key) {
                    case 'menuElement':
                        foreach ($value as $menuElRef => $menuElement) {
                            $menu->addMenuElement($this->createMenuElement($menuElement, $menuElRef));
                        }
                        break;
                    case 'user':
                        $menu->setUser($this->getReference($value));
                        break;
                    default:
                        $this->setData($key, $value, $menu);
                }
            }


            $manager->persist($menu);
            $this->addReference($ref, $menu);
        }

        $manager->flush();
    }

    /**
     * Créer un menuElement
     * @param array $data
     * @return MenuElement
     */
    private function createMenuElement(array $data, string $ref): MenuElement
    {
        $menuElement = new MenuElement();
        foreach ($data as $key => $value) {
            if ($key === 'menuElementTranslation') {
                foreach ($value as $menuElementTranslation) {
                    $menuElement->addMenuElementTranslation($this->populateEntity(
                        $menuElementTranslation, new MenuElementTranslation()));
                }
            } elseif ($key === 'page') {
                if (!empty($value)) {
                    $menuElement->setPage($this->getReference($value));
                }
            } elseif ($key === 'parent') {
                if (!empty($value)) {
                    $menuElement->setParent($this->getReference($value));
                }
            } else {
                $this->setData($key, $value, $menuElement);
            }
        }
        $this->addReference($ref, $menuElement);
        return $menuElement;
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
