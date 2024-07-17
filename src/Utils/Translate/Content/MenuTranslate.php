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
            'title_global_form' => $this->translator->trans('menu.title.global_form', domain: 'menu'),
            'error_empty_value' => $this->translator->trans('menu.error.empty.value', domain: 'menu'),
            'msg_wait_loading' => $this->translator->trans('menu.msg.wait.loading', domain: 'menu'),
            'btn_save' => $this->translator->trans('menu.btn.save', domain: 'menu'),
            'btn_new' => $this->translator->trans('menu.btn.new', domain: 'menu'),
            'no_select_menu_form' => $this->translator->trans('menu.no_select.menu.form', domain: 'menu'),
            'no_select_menu_form_msg' => $this->translator->trans('menu.no_select.menu.form.msg', domain: 'menu'),
            'help_title' => $this->translator->trans('menu.help.title', domain: 'menu'),
            'help_edition' => $this->translator->trans('menu.help.edition', domain: 'menu'),
            'help_delete' => $this->translator->trans('menu.help.delete', domain: 'menu'),
            'title_generic_data' => $this->translator->trans('menu.title.generic.data', domain: 'menu'),
            'input_name_label' => $this->translator->trans('menu.input.name.label', domain: 'menu'),
            'input_name_placeholder' => $this->translator->trans('menu.input.name.placeholder', domain: 'menu'),
            'input_name_error' => $this->translator->trans('menu.input.name.error', domain: 'menu'),
            'select_position_label' => $this->translator->trans('menu.select.position.label', domain: 'menu'),
            'select_type_label' => $this->translator->trans('menu.select.type.label', domain: 'menu'),
            'checkbox_disabled_label' => $this->translator->trans('menu.checkbox.disabled.label', domain: 'menu'),
            'checkbox_enabled_label' => $this->translator->trans('menu.checkbox.enabled.label', domain: 'menu'),
            'menu_form' => $this->getMenuFormTranslate(),
        ];
    }

    /**
     * Retourne les traductions pour le menu form
     * @return array
     */
    public function getMenuFormTranslate() :array
    {
        return [
            'title_edit' => $this->translator->trans('menu.form.title.edit', domain: 'menu'),
            'title_new' => $this->translator->trans('menu.form.title.new', domain: 'menu'),
            'input_search_page' => $this->translator->trans('menu.form.input.search.page', domain: 'menu'),
            'input_search_page_placeholder' => $this->translator->trans('menu.form.input.search.page.placeholder', domain: 'menu'),
            'block_fr' => $this->translator->trans('menu.form.block.fr', domain: 'menu'),
            'block_es' => $this->translator->trans('menu.form.block.es', domain: 'menu'),
            'block_en' => $this->translator->trans('menu.form.block.en', domain: 'menu'),
            'input_link_text' => $this->translator->trans('menu.form.input.link.text', domain: 'menu'),
            'input_link_url' => $this->translator->trans('menu.form.input.link.url', domain: 'menu'),
            'input_link_external_url' => $this->translator->trans('menu.form.input.link.external.url', domain: 'menu'),
            'radio_label_url_interne' => $this->translator->trans('menu.form.radio.label.url.interne', domain: 'menu'),
            'radio_label_url_externe' => $this->translator->trans('menu.form.radio.label.url.externe', domain: 'menu'),
            'title_position' => $this->translator->trans('menu.form.title.position', domain: 'menu'),
            'parent_label' => $this->translator->trans('menu.form.parent.label', domain: 'menu'),
            'parent_empty' => $this->translator->trans('menu.form.parent.empty', domain: 'menu'),
            'url_type_label' => $this->translator->trans('menu.form.url.type.label', domain: 'menu'),
            'position_column_label' =>  $this->translator->trans('menu.form.position.column.label', domain: 'menu'),
            'position_row_label' => $this->translator->trans('menu.form.position.row.label', domain: 'menu'),

        ];
    }
}
