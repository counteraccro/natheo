<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue pour Translate
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate\System;

use App\Utils\Translate\AppTranslate;

class TranslationTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour les traductions
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'translate_block_search_title' => $this->translator->trans(
                'translate.block.search.title',
                domain: 'translate',
            ),
            'translate_block_search_sub_title' => $this->translator->trans(
                'translate.block.search.sub.title',
                domain: 'translate',
            ),
            'translate_select_language_label' => $this->translator->trans(
                'translate.select.language.label',
                domain: 'translate',
            ),
            'translate_select_language' => $this->translator->trans('translate.select.language', domain: 'translate'),
            'translate_select_file' => $this->translator->trans('translate.select.file', domain: 'translate'),
            'translate_select_file_label' => $this->translator->trans(
                'translate.select.file.label',
                domain: 'translate',
            ),
            'translate_block_edit_sub_title' => $this->translator->trans(
                'translate.block.edit.sub.title',
                domain: 'translate',
            ),
            'translate_empty_file' => $this->translator->trans('translate.empty.file', domain: 'translate'),
            'translate_btn_save' => $this->translator->trans('translate.btn.save', domain: 'translate'),
            'translate_info_edit' => $this->translator->trans('translate.info.edit', domain: 'translate'),
            'translate_link_revert' => $this->translator->trans('translate.link.revert', domain: 'translate'),
            'translate_nb_edit' => $this->translator->trans('translate.nb.edit', domain: 'translate'),
            'translate_loading' => $this->translator->trans('translate.loading', domain: 'translate'),
            'translate_confirm_leave' => $this->translator->trans('translate.confirm.leave', domain: 'translate'),
            'translate_toast_title_success' => $this->translator->trans(
                'translate.toast.title.success',
                domain: 'translate',
            ),
            'translate_toast_title_error' => $this->translator->trans(
                'translate.toast.title.error',
                domain: 'translate',
            ),
            'translate_search_placeholder' => $this->translator->trans(
                'translate.search.placeholder',
                domain: 'translate',
            ),
            'translate_filter_untranslated' => $this->translator->trans(
                'translate.filter.untranslated',
                domain: 'translate',
            ),
            'translate_search_empty' => $this->translator->trans('translate.search.empty', domain: 'translate'),
            'translate_error_request' => $this->translator->trans('translate.error.request', domain: 'translate'),
            'translate_toast_time' => $this->translator->trans('translate.toast.time', domain: 'translate'),
        ];
    }
}
