<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixtures pour la génération des menus
 */

namespace App\DataFixtures\Admin\Content\Menu;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Menu\MenuElementTranslation;
use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Content\Menu\MenuLinkTarget;
use App\Enum\Admin\Content\Menu\MenuPosition;
use App\Enum\Admin\Content\Menu\MenuType;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MenuFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    /**
     * Dossier des menus : un fichier YAML par menu
     */
    const MENU_FIXTURES_FOLDER = 'content' . DIRECTORY_SEPARATOR . 'menu' . DIRECTORY_SEPARATOR;

    public function load(ObjectManager $manager): void
    {
        foreach ($this->loadYamlFolder($this->pathDataFixtures . self::MENU_FIXTURES_FOLDER) as $ref => $file) {
            $data = $file['data'];

            $menu = new Menu();
            $menu->setUser($this->getUserReference($data['user'] ?? 'User'));
            $menu->setName($data['name']);
            $menu->setType($this->enumValue(MenuType::class, $data['type']));
            $menu->setPosition($this->enumValue(MenuPosition::class, $data['position']));
            $menu->setRenderOrder($data['renderOrder'] ?? 1);
            $menu->setDefaultMenu($data['defaultMenu'] ?? false);
            $menu->setDisabled($data['disabled'] ?? false);
            $this->addMenuElements($menu, $data['elements'] ?? []);

            $manager->persist($menu);
            $this->addReference($ref, $menu);
        }

        $manager->flush();
    }

    /**
     * Ajoute au menu les éléments $elements et leurs enfants (clé « children »), dans l'ordre donné
     * @param Menu $menu
     * @param array $elements
     * @param MenuElement|null $parent
     * @return void
     */
    private function addMenuElements(Menu $menu, array $elements, ?MenuElement $parent = null): void
    {
        foreach (array_values($elements) as $index => $data) {
            $page = isset($data['page']) ? $this->getReference($data['page'], Page::class) : null;

            $menuElement = new MenuElement();
            $menuElement->setColumnPosition(1);
            $menuElement->setRowPosition($index + 1);
            $menuElement->setDisabled($data['disabled'] ?? false);
            $menuElement->setLinkTarget(
                $data['newTab'] ?? false
                    ? MenuLinkTarget::LINK_TARGET_BLANK->value
                    : MenuLinkTarget::LINK_TARGET_SELF->value,
            );
            $menuElement->setPage($page);
            $menuElement->setParent($parent);

            foreach ($this->getLocales() as $locale) {
                $translation = new MenuElementTranslation();
                $translation->setLocale($locale);
                $translation->setTextLink(
                    $data['label'][$locale] ?? ($page?->getPageTranslationByLocale($locale)?->getTitre() ?? ''),
                );
                $translation->setExternalLink($data['url'] ?? '#');
                $menuElement->addMenuElementTranslation($translation);
            }

            $menu->addMenuElement($menuElement);
            $this->addMenuElements($menu, $data['children'] ?? [], $menuElement);
        }
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
        return 298;
    }
}
