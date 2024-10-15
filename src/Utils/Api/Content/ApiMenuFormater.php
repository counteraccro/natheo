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
use App\Utils\Content\Menu\MenuConst;
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
        $i = 0;
        foreach ($elements as $element) {
            /** @var MenuElement $element */
            $return[$i] = [
                'col_position' => $element->getColumnPosition(),
                'row_position' => $element->getRowPosition(),
                'label' => $element->getMenuElementTranslationByLocale($this->locale)->getTextLink(),
            ];

            if (!$element->getChildren()->isEmpty()) {
                $return[$i]['elements'] = $this->getElements($element->getChildren()->toArray());
            }
            $i++;
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
            MenuConst::POSITION_HEADER => "HEADER",
            2 => "RIGHT",
            3 => "FOOTER",
            4 => "LEFT",
            default => "NONE",
        };
    }

    /*private function getStringType(int $type)
    {
        return match ($type) {

        }
    }*/

    /**
     * Retourne un menu formaté pour les API
     * @return array
     */
    public function getMenuFortApi()
    {
        return $this->return;
    }
}
