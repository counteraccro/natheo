<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue pour Menu
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Translate\Admin\Content;

use App\Translate\AppTranslate;

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
            'title_demo' => $this->translator->trans('menu.title.demo', domain: 'menu'),
            'title_demo_warning' => $this->translator->trans('menu.title.demo_warning', domain: 'menu'),
            'title_architecture' => $this->translator->trans('menu.title.architecture', domain: 'menu'),
            'title_global_form' => $this->translator->trans('menu.title.global_form', domain: 'menu'),
            'btn_save' => $this->translator->trans('menu.button.save', domain: 'menu'),
            'btn_new' => $this->translator->trans('menu.button.new', domain: 'menu'),
            'btn_back' => $this->translator->trans('menu.button.back', domain: 'menu'),
            'no_select_menu_form' => $this->translator->trans('menu.no_select.menu.form', domain: 'menu'),
            'no_select_menu_form_msg' => $this->translator->trans('menu.no_select.menu.form_message', domain: 'menu'),
            'no_select_menu_form_msg_2' => $this->translator->trans('menu.no_select.menu.form_message_2', domain: 'menu'),
            'no_select_menu_form_msg_3' => $this->translator->trans('menu.no_select.menu.form_message_3', domain: 'menu'),
            'no_select_menu_form_msg_4' => $this->translator->trans('menu.no_select.menu.form_message_4', domain: 'menu'),
            'no_select_menu_form_msg_5' => $this->translator->trans('menu.no_select.menu.form_message_5', domain: 'menu'),
            'no_select_menu_form_msg_6' => $this->translator->trans('menu.no_select.menu.form_message_6', domain: 'menu'),
            'input_name_label' => $this->translator->trans('menu.input.name.label', domain: 'menu'),
            'input_name_placeholder' => $this->translator->trans('menu.input.name.placeholder', domain: 'menu'),
            'input_name_error' => $this->translator->trans('menu.input.name.error', domain: 'menu'),
            'select_position_label' => $this->translator->trans('menu.select.position.label', domain: 'menu'),
            'select_type_label' => $this->translator->trans('menu.select.type.label', domain: 'menu'),
            'checkbox_disabled_label' => $this->translator->trans('menu.checkbox.disabled.label', domain: 'menu'),
            'checkbox_disabled_label_msg' => $this->translator->trans(
                'menu.checkbox.disabled.label_message',
                domain: 'menu',
            ),
            'checkbox_enabled_label_msg' => $this->translator->trans('menu.checkbox.enabled.label.message', domain: 'menu'),
            'checkbox_default_menu_true_label' => $this->translator->trans(
                'menu.checkbox.default.menu.true.label',
                domain: 'menu',
            ),
            'checkbox_default_menu_true_label_msg' => $this->translator->trans(
                'menu.checkbox.default.menu.false.label.message',
                domain: 'menu',
            ),
            'checkbox_default_menu_false_label_msg' => $this->translator->trans(
                'menu.checkbox.default.menu.true.label_message',
                domain: 'menu',
            ),
            'menu_element_confirm_delete_title' => $this->translator->trans(
                'menu.element.confirm.delete.title',
                domain: 'menu',
            ),
            'menu_element_confirm_delete_body' => $this->translator->trans(
                'menu.element.confirm.delete.body',
                domain: 'menu',
            ),
            'menu_element_confirm_delete_body_2' => $this->translator->trans(
                'menu.element.confirm.delete.body_2',
                domain: 'menu',
            ),
            'menu_element_confirm_delete_btn_ok' => $this->translator->trans(
                'menu.element.confirm.delete.button.ok',
                domain: 'menu',
            ),
            'menu_element_confirm_delete_btn_ko' => $this->translator->trans(
                'menu.element.confirm.delete.button.ko',
                domain: 'menu',
            ),
            'btn_new_menu_element' => $this->translator->trans('menu.button.new_menu_element', domain: 'menu'),
            'error' => $this->translator->trans('menu.label.error', domain: 'menu'),
            'error_info_label' => $this->translator->trans('menu.label.error_info_label', domain: 'menu'),
            'error_info_sub_label' => $this->translator->trans('menu.label.error_info_sub_label', domain: 'menu'),
            'error_no_menu_element_label' => $this->translator->trans(
                'menu.label.error_no_menu_element_label',
                domain: 'menu',
            ),
            'error_no_menu_element_sub_label' => $this->translator->trans(
                'menu.label.error_no_menu_element_sub_label',
                domain: 'menu',
            ),
            'no_save_label' => $this->translator->trans('menu.no.save.label', domain: 'menu'),
            'no_save_sub_label' => $this->translator->trans('menu.no.save.sub.label', domain: 'menu'),
            'menu_no_exist_title' => $this->translator->trans('menu.no.exist.title', domain: 'menu'),
            'menu_no_exist_text' => $this->translator->trans('menu.no.exist.text', domain: 'menu'),
            'menu_form' => $this->getMenuFormTranslate(),
            'menu_tree' => $this->getMenuTreeTranslate(),
        ];
    }

    /**
     * Retourne les traductions pour le menu form
     * @return array
     */
    public function getMenuFormTranslate(): array
    {
        return [
            'title_edit' => $this->translator->trans('menu.form.title.edit', domain: 'menu'),
            'input_search_page_placeholder' => $this->translator->trans(
                'menu.form.input.search.page.placeholder',
                domain: 'menu',
            ),
            'input_search_page_placeholder_no_result' => $this->translator->trans(
                'menu.form.input.search.page.placeholder_no_result',
                domain: 'menu',
            ),
            'input_link_text' => $this->translator->trans('menu.form.input.link.text', domain: 'menu'),
            'input_link_url' => $this->translator->trans('menu.form.input.link.url', domain: 'menu'),
            'input_link_external_url' => $this->translator->trans('menu.form.input.link.external.url', domain: 'menu'),
            'radio_label_url_interne' => $this->translator->trans('menu.form.radio.label.url.internal', domain: 'menu'),
            'radio_label_url_externe' => $this->translator->trans('menu.form.radio.label.url.external', domain: 'menu'),
            'url_type_label' => $this->translator->trans('menu.form.url.type.label', domain: 'menu'),
            'radio_label_disabled_element' => $this->translator->trans(
                'menu.form.radio.label.disabled.element',
                domain: 'menu',
            ),
            'radio_label_enabled_element' => $this->translator->trans(
                'menu.form.radio.label.enabled.element',
                domain: 'menu',
            ),
            'text_info_disabled_element' => $this->translator->trans(
                'menu.form.text.info.disabled.element',
                domain: 'menu',
            ),
            'text_info_enabled_element' => $this->translator->trans(
                'menu.form.text.info.enabled.element',
                domain: 'menu',
            ),
            'element_link_target_label' => $this->translator->trans('menu.form.link.target.label', domain: 'menu'),
            'element_link_target_label_self' => $this->translator->trans(
                'menu.form.link.target.label_self',
                domain: 'menu',
            ),
            'element_link_target_label_blank' => $this->translator->trans(
                'menu.form.link.target.label_blank',
                domain: 'menu',
            ),
            'btn_save' => $this->translator->trans('menu.form.button.save', domain: 'menu'),
            'btn_cancel' => $this->translator->trans('menu.form.button.cancel', domain: 'menu'),
            'btn_delete' => $this->translator->trans('menu.form.button.delete', domain: 'menu'),
            'empty_text_link_error' => $this->translator->trans('menu.form.empty.text.link.error', domain: 'menu'),
            'invalid_external_link_error' => $this->translator->trans(
                'menu.form.invalid.external.link.error',
                domain: 'menu',
            ),
            'empty_internal_link_error' => $this->translator->trans(
                'menu.form.empty.internal.link.error',
                domain: 'menu',
            ),
        ];
    }

    /**
     * Traduction pour l'arbre de menuElement
     * @return array
     */
    public function getMenuTreeTranslate(): array
    {
        return [
            'btn_new_menu_element' => $this->translator->trans('menu.tree.button.new.menu.element', domain: 'menu'),
            'tag_page' => $this->translator->trans('menu.tree.tag.page', domain: 'menu'),
            'tag_link' => $this->translator->trans('menu.tree.tag.link', domain: 'menu'),
            'menu_element_disabled' => $this->translator->trans('menu.tree.element.disabled', domain: 'menu'),
            'menu_element_no_save' => $this->translator->trans('menu.tree.element.no.save', domain: 'menu'),
        ];
    }
}
