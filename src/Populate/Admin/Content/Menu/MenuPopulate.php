<?php

declare(strict_types=1);
/**
 * Permet de merger les données venant d'un tableau à un objet menu
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Populate\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Menu\MenuElementTranslation;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Trait\PopulateTrait;
use App\Service\Admin\Content\Menu\MenuService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MenuPopulate
{
    use PopulateTrait;

    /**
     * Champs modifiables de Menu
     * @var string[]
     */
    private const array FIELDS_MENU = ['name', 'type', 'position', 'renderOrder', 'defaultMenu', 'disabled'];

    /**
     * Champs modifiables de MenuElement
     * @var string[]
     */
    private const array FIELDS_MENU_ELEMENT = ['columnPosition', 'rowPosition', 'linkTarget', 'disabled'];

    /**
     * Champs modifiables de MenuElementTranslation
     * @var string[]
     */
    private const array FIELDS_MENU_ELEMENT_TRANSLATION = ['locale', 'textLink', 'externalLink'];

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
     * menuElements existants du menu, indexés par id
     * @var array<int, MenuElement>
     */
    private array $existingElements = [];

    /**
     * Ids des menuElements existants présents dans les données reçues
     * @var int[]
     */
    private array $keptElementIds = [];

    /**
     * @param Menu $menu
     * @param array $populate
     * @param MenuService $menuService
     */
    public function __construct(
        private Menu $menu,
        private readonly array $populate,
        private readonly MenuService $menuService,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
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
        $populate = $this->castValuesToInt($this->populate, ['type', 'position']);
        $this->menu = $this->mergeData($this->menu, $populate, self::FIELDS_MENU);
    }

    /**
     * Met à jour les liaisons entre page et menu ; les ids invalides sont ignorés
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function populatePageMenu(): void
    {
        if (!isset($this->populate[self::KEY_PAGE_MENU]) || !is_array($this->populate[self::KEY_PAGE_MENU])) {
            return;
        }

        foreach ($this->menu->getPages() as $page) {
            $page->removeMenu($this->menu);
            $this->menu->removePage($page);
        }

        foreach ($this->populate[self::KEY_PAGE_MENU] as $id) {
            if (!is_numeric($id) || (int) $id <= 0) {
                continue;
            }
            /** @var Page|null $page */
            $page = $this->menuService->findOneById(Page::class, (int) $id);
            if ($page === null) {
                continue;
            }
            $this->menu->addPage($page);
            $page->addMenu($this->menu);
        }
    }

    /**
     * Met à jour les menuElements : les éléments existants sont mis à jour par id (leurs ids ne changent pas),
     * les nouveaux sont créés et les absents des données sont supprimés
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function populateMenuElement(): void
    {
        if (
            !isset($this->populate[self::KEY_MENU_ELEMENTS]) ||
            !is_array($this->populate[self::KEY_MENU_ELEMENTS]) ||
            count($this->populate[self::KEY_MENU_ELEMENTS]) === 0
        ) {
            return;
        }

        $this->collectExistingElements($this->menu->getMenuElements()->toArray());

        foreach ($this->populate[self::KEY_MENU_ELEMENTS] as $populateMenuElement) {
            $this->populateOneMenuElement($populateMenuElement, null);
        }

        foreach ($this->existingElements as $id => $menuElement) {
            if (!in_array($id, $this->keptElementIds, true)) {
                $this->removeMenuElement($menuElement);
            }
        }
    }

    /**
     * Indexe par id les menuElements existants, enfants compris
     * @param MenuElement[] $menuElements
     * @return void
     */
    private function collectExistingElements(array $menuElements): void
    {
        foreach ($menuElements as $menuElement) {
            if ($menuElement->getId() === null || isset($this->existingElements[$menuElement->getId()])) {
                continue;
            }
            $this->existingElements[$menuElement->getId()] = $menuElement;
            $this->collectExistingElements($menuElement->getChildren()->toArray());
        }
    }

    /**
     * Met à jour (ou crée si son id est inconnu) un menuElement et ses enfants
     * @param array $populateElement données de l'élément
     * @param MenuElement|null $parent parent de l'élément, null pour un élément de premier niveau
     * @return MenuElement
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function populateOneMenuElement(array $populateElement, ?MenuElement $parent): MenuElement
    {
        // Les nouveaux éléments arrivent avec un id temporaire négatif
        $id = (int) ($populateElement['id'] ?? 0);
        $menuElement = $this->existingElements[$id] ?? null;
        if ($menuElement === null) {
            $menuElement = new MenuElement();
        } else {
            $this->keptElementIds[] = $id;
        }
        $this->menu->addMenuElement($menuElement);

        $this->mergeData($menuElement, $populateElement, self::FIELDS_MENU_ELEMENT);
        $this->setPageToMenuElement($menuElement, $populateElement['page'] ?? null);
        $this->populateMenuElementTranslation(
            $populateElement[self::KEY_MENU_ELEMENTS_TRANSLATIONS] ?? [],
            $menuElement,
        );

        $oldParent = $menuElement->getParent();
        if ($oldParent !== $parent) {
            // Sinon la cascade « remove » de l'ancien parent supprimerait l'élément déplacé
            $oldParent?->removeChild($menuElement);
            $menuElement->setParent($parent);
            $parent?->addChild($menuElement);
        }

        foreach ($populateElement[self::KEY_MENU_ELEMENTS_CHILDREN] ?? [] as $populateChild) {
            $this->populateOneMenuElement($populateChild, $menuElement);
        }
        return $menuElement;
    }

    /**
     * Supprime un menuElement absent des données reçues
     * @param MenuElement $menuElement
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function removeMenuElement(MenuElement $menuElement): void
    {
        $menuElement->getParent()?->removeChild($menuElement);
        if ($this->menu->getMenuElements()->contains($menuElement)) {
            // orphanRemoval de Menu::$menuElements supprime l'élément au flush
            $this->menu->removeMenuElement($menuElement);
            return;
        }
        $this->menuService->remove($menuElement, false);
    }

    /**
     * Met à jour les traductions d'un menuElement par locale, en créant celles qui manquent
     * @param array $populateMenuElementTranslations
     * @param MenuElement $menuElement
     * @return void
     */
    private function populateMenuElementTranslation(
        array $populateMenuElementTranslations,
        MenuElement $menuElement,
    ): void {
        foreach ($populateMenuElementTranslations as $populateMenuElementTranslation) {
            $locale = $populateMenuElementTranslation['locale'] ?? null;
            if ($locale === null) {
                continue;
            }

            // Recherche exacte : getMenuElementTranslationByLocale() se replie sur une autre langue
            $menuElementTranslation = $menuElement
                ->getMenuElementTranslations()
                ->findFirst(fn(int $key, MenuElementTranslation $translation) => $translation->getLocale() === $locale);
            if ($menuElementTranslation === null) {
                $menuElementTranslation = new MenuElementTranslation();
                $menuElement->addMenuElementTranslation($menuElementTranslation);
            }
            $this->mergeData(
                $menuElementTranslation,
                $populateMenuElementTranslation,
                self::FIELDS_MENU_ELEMENT_TRANSLATION,
            );
        }
    }

    /**
     * Met à jour la page associée à un menuElement ; un id vide, négatif ou inconnu retire la page
     * @param MenuElement $menuElement
     * @param mixed $pageId
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function setPageToMenuElement(MenuElement $menuElement, mixed $pageId): void
    {
        $page = null;
        if (is_numeric($pageId) && (int) $pageId > 0) {
            $page = $this->menuService->findOneById(Page::class, (int) $pageId);
        }
        $menuElement->setPage($page);
    }

    /**
     * Convertit en entier les valeurs des clés $keys
     * @param array $data
     * @param array $keys
     * @return array
     */
    private function castValuesToInt(array $data, array $keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = (int) $data[$key];
            }
        }
        return $data;
    }
}
