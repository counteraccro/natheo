<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour FAQ
 * @author Gourdon Aymeric
 * @version 1.0
 */
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
            'newFaq' => $this->getNewFaqTranslate(),
            'editFaq' => $this->getEditFaqTranslate(),
        ];
    }

    private function getNewFaqTranslate()
    {
        return [
            'new_faq' => $this->translator->trans('faq.new.title', domain: 'faq'),
            'new_faq_sub_title' => $this->translator->trans('faq.new.sub_title', domain: 'faq'),
            'new_faq_input_title' => $this->translator->trans('faq.new.input.title', domain: 'faq'),
            'new_faq_help' => $this->translator->trans('faq.new.help', domain: 'faq'),
            'new_faq_btn' => $this->translator->trans('faq.new.btn', domain: 'faq'),
            'new_faq_btn_cancel' => $this->translator->trans('faq.new.btn.cancel', domain: 'faq'),
            'new_faq_error_empty' => $this->translator->trans('faq.new.input.error.empty', domain: 'faq'),
        ];
    }

    private function getEditFaqTranslate(): array
    {
        return [
            'edit_faq' => $this->translator->trans('faq.edit.title', domain: 'faq'),
            'edit_faq_sub_title' => $this->translator->trans('faq.edit.sub_title', domain: 'faq'),
            'input_faq_title' => $this->translator->trans('faq.edit.input.title', domain: 'faq'),
            'input_faq_title_help' => $this->translator->trans('faq.edit.input.title.help', domain: 'faq'),
            'input_faq_title_error' => $this->translator->trans('faq.edit.input.title.error', domain: 'faq'),
            'select_locale' => $this->translator->trans('faq.select.locale', domain: 'faq'),
            'btn_save' => $this->translator->trans('faq.edit.btn.save', domain: 'faq'),
            'btn_cancel' => $this->translator->trans('faq.edit.btn.cancel', domain: 'faq'),
            'text_no_change' => $this->translator->trans('faq.edit.text.no_change', domain: 'faq'),
            'text_change_no_save' => $this->translator->trans('faq.edit.text.change_no_save', domain: 'faq'),
            'text_error' => $this->translator->trans('faq.edit.text.error', domain: 'faq'),
            'text_save_success' => $this->translator->trans('faq.edit.text.save.success', domain: 'faq'),
            'no_questions' => $this->translator->trans('faq.edit.no.questions', domain: 'faq'),
            'one_question' => $this->translator->trans('faq.edit.one.question', domain: 'faq'),
            'multiple_questions' => $this->translator->trans('faq.edit.multiple.questions', domain: 'faq'),
            'question_label' => $this->translator->trans('faq.edit.question.label', domain: 'faq'),
            'answer_label' => $this->translator->trans('faq.edit.answer.label', domain: 'faq'),
            'question_error' => $this->translator->trans('faq.edit.question.error', domain: 'faq'),
            'text_error_not_question' => $this->translator->trans('faq.edit.text.error.not.question', domain: 'faq'),
            'btn_new_question' => $this->translator->trans('faq.edit.btn.new.question', domain: 'faq'),
            'btn_new_category' => $this->translator->trans('faq.edit.btn.new.category', domain: 'faq'),
            'new_question_fr' => $this->translator->trans('faq.edit.new.question.fr', domain: 'faq'),
            'new_question_es' => $this->translator->trans('faq.edit.new.question.es', domain: 'faq'),
            'new_question_en' => $this->translator->trans('faq.edit.new.question.en', domain: 'faq'),
            'new_answer_fr' => $this->translator->trans('faq.edit.new.answer.fr', domain: 'faq'),
            'new_answer_en' => $this->translator->trans('faq.edit.new.answer.en', domain: 'faq'),
            'new_answer_es' => $this->translator->trans('faq.edit.new.answer.es', domain: 'faq'),
            'new_category_fr' => $this->translator->trans('faq.edit.new.category.fr', domain: 'faq'),
            'new_category_en' => $this->translator->trans('faq.edit.new.category.en', domain: 'faq'),
            'new_category_es' => $this->translator->trans('faq.edit.new.category.es', domain: 'faq'),
        ];
    }
}
