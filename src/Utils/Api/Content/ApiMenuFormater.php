<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Converti un menu en array pour le retour API
 */
namespace App\Utils\Api\Content;

use App\Entity\Admin\Content\Menu\Menu;

class ApiMenuFormater
{

    private $return = [];

    public function __construct(private Menu $menu, private string $locale)
    {
    }

    /**
     * @return $this
     */
    public function convertMenu()
    {
        $this->return['aa'] = $this->menu->getName();
        return $this;
    }

    /**
     * Retourne un menu formaté pour les API
     * @return array
     */
    public function getMenuFortApi()
    {
        return $this->return;
    }
}
