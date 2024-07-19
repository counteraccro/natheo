<?php
/**
 * Permet de merger les données venant d'un tableau à un objet menu
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;

class MenuPopulate
{
    /**
     * @param Menu $menu
     * @param array $populate
     */
    public function __construct(private Menu $menu, private array $populate)
    {
    }

    public function populate(): static
    {
        $this->populateMenu();
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
        $this->menu = $this->mergeData($this->menu, $this->populate, ['menuElements', 'refChilds', 'id']);
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

            $func = 'set' . ucfirst($key);
            $object->$func($value);
        }
        return $object;
    }
}
