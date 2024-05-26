<?php

namespace App\Utils\Translate\Content;

use App\Utils\Translate\AppTranslate;

class FaqTranslate extends AppTranslate
{
    /**
     * Retourne un tableau de translate pour la FAQ
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'select_locale' => $this->translator->trans('faq.select.locale', domain: 'faq'),
            'loading' => $this->translator->trans('faq.select.loading', domain: 'faq'),
            'error_empty_value' => $this->translator->trans('faq.error.empty.value', domain: 'faq'),
            'msg_no_save' => $this->translator->trans('faq.msg.no.save', domain: 'faq'),
            'save' => $this->translator->trans('faq.save', domain: 'faq'),
            'new_faq' => $this->translator->trans('faq.new.title', domain: 'faq'),
            'new_faq_input_title' => $this->translator->trans('faq.new.input.title', domain: 'faq'),
            'new_faq_help' => $this->translator->trans('faq.new.help', domain: 'faq'),
            'new_faq_btn' => $this->translator->trans('faq.new.btn', domain: 'faq'),
            'new_category_btn' => $this->translator->trans('faq.new.category.btn', domain: 'faq'),
            'new_faq_error_empty' => $this->translator->trans('faq.new.input.error.empty', domain: 'faq'),
            'toast_title_success' => $this->translator->trans('faq.toast.title.success', domain: 'faq'),
            'toast_time' => $this->translator->trans('faq.toast.time', domain: 'faq'),
            'toast_title_error' => $this->translator->trans('faq.toast.title.error', domain: 'faq'),
            'faq_disabled_btn_ok' => $this->translator->trans('faq.disabled_btn_ok', domain: 'faq'),
            'faq_disabled_btn_ko' => $this->translator->trans('faq.disabled_btn_ko', domain: 'faq'),
            'faq_question_disabled' => $this->translator->trans('faq.question.disabled', domain: 'faq'),
            'faq_category_disabled' => $this->translator->trans('faq.category.disabled', domain: 'faq'),
            'faq_category_disabled_title' => $this->translator->trans('faq.category.disabled.title', domain: 'faq'),
            'faq_category_disabled_message' => $this->translator->trans('faq.category.disabled.message', domain: 'faq'),
            'faq_question_disabled_title' => $this->translator->trans('faq.question.disabled.title', domain: 'faq'),
            'faq_question_disabled_message' => $this->translator->trans('faq.question.disabled.message', domain: 'faq'),
            'faq_category_enabled_title' => $this->translator->trans('faq.category.enabled.title', domain: 'faq'),
            'faq_category_enabled_message' => $this->translator->trans('faq.category.enabled.message', domain: 'faq'),
            'faq_category_enabled_message_2' =>
                $this->translator->trans('faq.category.enabled.message.2', domain: 'faq'),
            'faq_question_enabled_title' => $this->translator->trans('faq.question.enabled.title', domain: 'faq'),
            'faq_question_enabled_message' => $this->translator->trans('faq.question.enabled.message', domain: 'faq'),
            'faq_category_new_title' => $this->translator->trans('faq.category.new.title', domain: 'faq'),
            'faq_category_new_liste_cat' => $this->translator->trans('faq.category.new.liste.cat', domain: 'faq'),
            'faq_category_new_after' => $this->translator->trans('faq.category.new.after', domain: 'faq'),
            'faq_category_new_before' => $this->translator->trans('faq.category.new.before', domain: 'faq'),
            'faq_category_new_help' => $this->translator->trans('faq.category.new.help', domain: 'faq'),
            'faq_category_new_btn_validate' => $this->translator->trans('faq.category.new.btn.validate', domain: 'faq'),
            'faq_category_new_btn_cancel' => $this->translator->trans('faq.category.new.btn.cancel', domain: 'faq'),
            'faq_question_new_title' => $this->translator->trans('faq.question.new.title', domain: 'faq'),
            'faq_question_new_liste_cat' => $this->translator->trans('faq.question.new.liste.cat', domain: 'faq'),
            'faq_question_new_after' => $this->translator->trans('faq.question.new.after', domain: 'faq'),
            'faq_question_new_before' => $this->translator->trans('faq.question.new.before', domain: 'faq'),
            'faq_question_new_help' => $this->translator->trans('faq.question.new.help', domain: 'faq'),
            'faq_question_new_btn_validate' => $this->translator->trans('faq.question.new.btn.validate', domain: 'faq'),
            'faq_question_new_btn_cancel' => $this->translator->trans('faq.question.new.btn.cancel', domain: 'faq'),
            'faq_question_delete_titre_confirm' =>
                $this->translator->trans('faq.question.delete.titre.confirm', domain: 'faq'),
            'faq_question_delete_body_confirm' =>
                $this->translator->trans('faq.question.delete.body.confirm', domain: 'faq'),
            'faq_category_delete_titre_confirm' =>
                $this->translator->trans('faq.category.delete.titre.confirm', domain: 'faq'),
            'faq_category_delete_body_confirm' =>
                $this->translator->trans('faq.category.delete.body.confirm', domain: 'faq'),

        ];
    }
}
