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
            'title_demo' => $this->translator->trans('menu.title.demo', domain: 'menu'),
            'title_demo_warning' => $this->translator->trans('menu.title.demo_warning', domain: 'menu'),
            'title_architecture' => $this->translator->trans('menu.title.architecture', domain: 'menu'),
            'error_empty_value' => $this->translator->trans('menu.error.empty.value', domain: 'menu'),
            'msg_wait_loading' => $this->translator->trans('menu.msg.wait.loading', domain: 'menu'),
            'btn_save' => $this->translator->trans('menu.btn.save', domain: 'menu'),
            'btn_new' => $this->translator->trans('menu.btn.new', domain: 'menu'),
        ];
    }
}
