<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Création d'un jeu de données pour les menus
 */

namespace App\Tests\Helper\Fixtures\Content;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Menu\MenuElementTranslation;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;
use App\Utils\Content\Menu\MenuConst;

trait MenuFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'un menu
     * @param User|null $user
     * @param array $customData
     * @param bool $persist
     * @return Menu
     */
    public function createMenu(User $user = null, array $customData = [], bool $persist = true): Menu
    {
        if ($user === null) {
            $user = $this->createUserContributeur();
        }


        $data = [
            'user' => $user,
            'name' => self::getFaker()->text(),
            'type' => MenuConst::TYPE_HEADER_SIDE_BAR,
            'position' => MenuConst::POSITION_HEADER,
            'renderOrder' => 1,
            'defaultMenu' => false,
            'disabled' => self::getFaker()->boolean(),
        ];

        $menu = $this->initEntity(Menu::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($menu);
        }
        return $menu;
    }

    /**
     * Création d'un menu element
     * @param Menu|null $menu
     * @param MenuElement|null $parent
     * @param Page|null $page
     * @param array $customData
     * @param bool $persist
     * @return MenuElement
     */
    public function createMenuElement(Menu $menu = null, MenuElement $parent = null, Page $page = null, array $customData = [], bool $persist = true): MenuElement
    {
        if ($menu === null) {
            $menu = $this->createMenu();
        }

        $linkTargetArray = [
            MenuConst::LINK_TARGET_SELF, MenuConst::LINK_TARGET_BLANK
        ];
        $linkTarget = $linkTargetArray[array_rand($linkTargetArray)];

        $data = [
            'menu' => $menu,
            'parent' => $parent,
            'page' => $page,
            'columnPosition' => 1,
            'rowPosition' => 1,
            'linkTarget' => $linkTarget,
            'disabled' => self::getFaker()->boolean(),
        ];
        $menuElement = $this->initEntity(MenuElement::class, array_merge($data, $customData));
        $menu->addMenuElement($menuElement);

        $parent?->addChild($menuElement);
        $page?->addMenuElement($menuElement);

        if ($persist) {
            $this->persistAndFlush($menuElement);
        }
        return $menuElement;
    }

    /**
     * Créer un menuElementTranslation
     * @param MenuElement|null $menuElement
     * @param array $customData
     * @param bool $persist
     * @return MenuElementTranslation
     */
    public function createMenuElementTranslation(MenuElement $menuElement = null, array $customData = [], bool $persist = true): MenuElementTranslation
    {
        if ($menuElement === null) {
            $menuElement = $this->createMenuElement();
        }

        $data = [
            'menuElement' => $menuElement,
            'locale' => self::getFaker()->locale(),
            'textLink' => self::getFaker()->text(),
            'externalLink' => self::getFaker()->url(),
        ];

        $menuElementTranslation = $this->initEntity(MenuElementTranslation::class, array_merge($data, $customData));
        /** @var MenuElementTranslation $menuElementTranslation */
        $menuElement->addMenuElementTranslation($menuElementTranslation);

        if ($persist) {
            $this->persistAndFlush($menuElementTranslation);
        }
        return $menuElementTranslation;
    }

    /**
     * Créer un jeu de donnée complet d'un menu
     * @return Menu
     */
    public function createMenuAllDataDefault() : Menu
    {
        $menu = $this->createMenu();
        $menuElement = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 1, 'disabled' => false]);
        foreach($this->locales as $locale) {
            $this->createMenuElementTranslation($menuElement, ['locale' => $locale]);
        }

        $menuElement2 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 2, 'disabled' => false]);
        foreach($this->locales as $locale) {
            $this->createMenuElementTranslation($menuElement2, ['locale' => $locale]);
        }

        $menuElement3 = $this->createMenuElement($menu, customData: ['columnPosition' => 2, 'rowPosition' => 1, 'disabled' => false]);
        foreach($this->locales as $locale) {
            $this->createMenuElementTranslation($menuElement3, ['locale' => $locale]);
        }

        $subMenuElement = $this->createMenuElement($menu, $menuElement3, customData: ['columnPosition' => 1, 'rowPosition' => 1, 'disabled' => false]);
        foreach($this->locales as $locale) {
            $this->createMenuElementTranslation($subMenuElement, ['locale' => $locale, 'textLink' => 'enfant']);
        }

        $menuElement4 = $this->createMenuElement($menu, customData: ['columnPosition' => 3, 'rowPosition' => 1, 'disabled' => true]);
        foreach($this->locales as $locale) {
            $this->createMenuElementTranslation($menuElement4, ['locale' => $locale]);
        }

        return $menu;
    }
}