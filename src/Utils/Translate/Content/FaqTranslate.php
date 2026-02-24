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
            'select_locale' => $this->translator->trans('faq.select.locale', domain: 'faq'),
        ];
    }
}
