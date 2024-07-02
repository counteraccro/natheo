<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour Menu
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Translate\Content;

use App\Utils\Translate\AppTranslate;

class MenuTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour les menus
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'select_locale' => $this->translator->trans('menu.select.locale', domain: 'menu'),
            'select_position' => $this->translator->trans('menu.select.position', domain: 'menu'),
            'select_type' => $this->translator->trans('menu.select.type', domain: 'menu'),
        ];
    }
}
