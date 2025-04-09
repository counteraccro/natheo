<?php
/**
 * Permet de merger les données venant d'un tableau à un objet menu
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Menu\MenuElementTranslation;
use App\Entity\Admin\Content\Page\Page;
use App\Service\Admin\Content\Menu\MenuService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MenuPopulate
{

    /**
     * Clé pour les menuElements
     * @var string
     */
    private const KEY_MENU_ELEMENTS = 'menuElements';

    /**
     * Clé pour les menuElementTranslations
     * @var string
     */
    private const KEY_MENU_ELEMENTS_TRANSLATIONS = 'menuElementTranslations';

    /**
     * Clé pour les menuElement enfants
     * @var string
     */
    private const KEY_MENU_ELEMENTS_CHILDREN = 'children';

    /**
     * Clé pour les pageMenu
     * @var string
     */
    private const KEY_PAGE_MENU = 'pageMenu';

    /**
     * @param Menu $menu
     * @param array $populate
     * @param MenuService $menuService
     */
    public function __construct(
        private Menu                 $menu,
        private readonly array       $populate,
        private readonly MenuService $menuService)
    {
    }

    public function populate(): static
    {
        $this->populateMenu();
        $this->populateMenuElement();
        $this->populatePageMenu();
        return $this;
    }

    /**
     * Retourne le menu
     * @return Menu
     */
    public function getMenu(): Menu
    {
        return $this->menu;
    }

    /**
     * Met à jour les données du menu
     * @return void
     */
    private function populateMenu(): void
    {
        $this->menu = $this->mergeData($this->menu, $this->populate, [self::KEY_MENU_ELEMENTS, 'refChilds', 'id']);
    }

    /**
     * Met à jour les liaisons entre page et menu
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function populatePageMenu(): void
    {
        if(isset($this->populate[self::KEY_PAGE_MENU])){

            foreach($this->menu->getPages() as $page)
            {
                $page->removeMenu($this->menu);
                $this->menu->removePage($page);
            }

            if(!in_array('-1', $this->populate[self::KEY_PAGE_MENU])){
                foreach($this->populate[self::KEY_PAGE_MENU] as $id){
                    /** @var Page $page */
                    $page = $this->menuService->findOneById(Page::class, $id);
                    $this->menu->addPage($page);
                    $page->addMenu($this->menu);
                }
            }
        }
    }

    /**
     * Met à jour les menuElements
     * @return void
     */
    private function populateMenuElement(): void
    {
        if (!isset($this->populate[self::KEY_MENU_ELEMENTS]) || !is_array($this->populate[self::KEY_MENU_ELEMENTS])
            || count($this->populate[self::KEY_MENU_ELEMENTS]) === 0) {
            return;
        }

        $this->menu->getMenuElements()->clear();

        foreach ($this->populate[self::KEY_MENU_ELEMENTS] as $populateMenuElement) {
            $menuElement = $this->createMenuElement($populateMenuElement);
            $this->menu->addMenuElement($menuElement);

        }
    }

    /**
     * Créer un objet menuElement
     * @param mixed $populateChildren
     * @return MenuElement
     */
    private function createMenuElement(array $populateChildren): MenuElement
    {
        $menuElement = new MenuElement();
        $menuElement->setMenu($this->menu);

        $menuElement = $this->mergeData($menuElement, $populateChildren,
            [self::KEY_MENU_ELEMENTS_CHILDREN, self::KEY_MENU_ELEMENTS_TRANSLATIONS, 'id', 'page', 'parent', 'refChilds']
        );
        $menuElement = $this->setPageToMenuElement($menuElement, $populateChildren['page']);

        $menuElement = $this->populateMenuElementTranslation($populateChildren[self::KEY_MENU_ELEMENTS_TRANSLATIONS], $menuElement);

        if (isset($populateChildren[self::KEY_MENU_ELEMENTS_CHILDREN]) && count($populateChildren[self::KEY_MENU_ELEMENTS_CHILDREN]) > 0) {
            $menuElement = $this->populateMenuElementChildren($populateChildren[self::KEY_MENU_ELEMENTS_CHILDREN], $menuElement);
        }
        return $menuElement;
    }

    /**
     * Met à jour les menusElement enfants
     * @param array $populateMenuElementChildren
     * @param MenuElement $parent
     * @return MenuElement
     */
    private function populateMenuElementChildren(array $populateMenuElementChildren, MenuElement $parent): MenuElement
    {
        foreach ($populateMenuElementChildren as $populateChildren) {
            $children = $this->createMenuElement($populateChildren);
            $children->setParent($parent);
            $parent->addChild($children);
        }
        return $parent;
    }

    /**
     * Merge les données pour les traductions
     * @param array $populateMenuElementTranslations
     * @param MenuElement $menuElement
     * @return MenuElement
     */
    private function populateMenuElementTranslation(array $populateMenuElementTranslations, MenuElement $menuElement): MenuElement
    {
        foreach ($populateMenuElementTranslations as $populateMenuElementTranslation) {
            $menuElementTranslation = new MenuElementTranslation();
            $menuElementTranslation = $this->mergeData($menuElementTranslation, $populateMenuElementTranslation, ['id', 'link']);
            $menuElementTranslation->setMenuElement($menuElement);
            $menuElement->addMenuElementTranslation($menuElementTranslation);
        }
        return $menuElement;
    }

    /**
     * Met à jour de la page associé à un menuElement
     * @param MenuElement $menuElement
     * @param int|null $pageId
     * @return MenuElement
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function setPageToMenuElement(MenuElement $menuElement, mixed $pageId = ""): MenuElement
    {
        if ($pageId === "" || $pageId < 0) {
            return $menuElement;
        }

        $page = $this->menuService->findOneById(Page::class, $pageId);
        if ($page !== null) {
            $menuElement->setPage($page);
        }
        return $menuElement;
    }

    /**
     * Merge des données de $populate dans $object sans prendre en compte $exclude
     * @param mixed $object
     * @param array $populate
     * @param array $exclude
     * @return mixed
     */
    private function mergeData(mixed $object, array $populate, array $exclude = []): mixed
    {
        foreach ($populate as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }

            if (is_array($value)) {
                continue;
            }

            $func = 'set' . ucfirst($key);
            $object->$func($value);
        }
        return $object;
    }
}
