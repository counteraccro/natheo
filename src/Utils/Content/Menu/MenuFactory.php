<?php
/**
 * Permet de créer un menu et l'ensemble des données associées
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Menu;

use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Menu\MenuElementTranslation;

class MenuFactory
{
    public function __construct(private readonly array $locales) {}

    /**
     * Créer un menuElement
     * @return MenuElement
     */
    public function createMenuElement(): MenuElement
    {
        $menuElement = new MenuElement();
        $menuElement->setDisabled(false);
        return $this->createMenuElementTranslations($menuElement);
    }

    /**
     * Créer des objets menuElementTranslation
     * @param MenuElement $menuElement
     * @return MenuElement
     */
    private function createMenuElementTranslations(MenuElement $menuElement): MenuElement
    {
        foreach ($this->locales as $locale) {
            $menuElementTranslation = new MenuElementTranslation();
            $menuElementTranslation->setLocale($locale);
            $menuElementTranslation->setMenuElement($menuElement);
            $menuElementTranslation->setTextLink($locale . '- new');
            $menuElement->addMenuElementTranslation($menuElementTranslation);
        }
        return $menuElement;
    }
}
