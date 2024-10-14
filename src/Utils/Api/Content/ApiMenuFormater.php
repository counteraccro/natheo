<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Converti un menu en array pour le retour API
 */

namespace App\Utils\Api\Content;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Menu\MenuElementTranslation;
use Doctrine\Common\Collections\Collection;

class ApiMenuFormater
{

    private array $return = [];

    /**
     * @param array $menu
     * @param string $locale
     */
    public function __construct(
        private readonly array  $menu,
        private readonly string $locale)
    {
    }

    /**
     * Converti un menu en array pour API
     * @return $this
     */
    public function convertMenu(): static
    {
        $this->return['position'] = $this->getStringPosition($this->menu['position']);
        $this->return['elements'] = $this->getElements($this->menu['menuElements']);
        return $this;
    }

    /**
     * Génères un array avec les elements
     * @param array $elements
     * @return array
     */
    private function getElements(array $elements): array
    {
        $return = [];
        foreach ($elements as $element) {
            /** @var MenuElement $element */
            $return[] = [
                'col_position' => $element->getColumnPosition(),
                'row_position' => $element->getRowPosition(),
                'label' => $element->getMenuElementTranslationByLocale($this->locale)->getTextLink()
            ];
        }

        return $return;
    }

    /**
     * Converti une position en string
     * @param int $position
     * @return string
     */
    private function getStringPosition(int $position): string
    {
        return match ($position) {
            1 => "HEADER",
            2 => "RIGHT",
            3 => "FOOTER",
            4 => "LEFT",
            default => "NONE",
        };
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
