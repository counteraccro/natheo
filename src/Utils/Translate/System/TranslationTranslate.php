<?php
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
            'translate_select_language' => $this->translator->trans('translate.select.language', domain: 'translate'),
            'translate_select_file' => $this->translator->trans('translate.select.file', domain: 'translate'),
            'translate_empty_file' => $this->translator->trans('translate.empty.file', domain: 'translate'),
            'translate_btn_save' => $this->translator->trans('translate.btn.save', domain: 'translate'),
            'translate_btn_cache' => $this->translator->trans('translate.btn.cache', domain: 'translate'),
            'translate_info_edit' => $this->translator->trans('translate.info.edit', domain: 'translate'),
            'translate_link_revert' => $this->translator->trans('translate.link.revert', domain: 'translate'),
            'translate_nb_edit' => $this->translator->trans('translate.nb.edit', domain: 'translate'),
            'translate_loading' => $this->translator->trans('translate.loading', domain: 'translate'),
            'translate_cache_titre' => $this->translator->trans('translate.cache.titre', domain: 'translate'),
            'translate_cache_info' => $this->translator->trans('translate.cache.info', domain: 'translate'),
            'translate_cache_wait' => $this->translator->trans('translate.cache.wait', domain: 'translate'),
            'translate_cache_btn_close' => $this->translator->trans('translate.cache.btn.close', domain: 'translate'),
            'translate_cache_btn_accept' => $this->translator->trans('translate.cache.btn.accept', domain: 'translate'),
            'translate_cache_success' => $this->translator->trans('translate.cache.success', domain: 'translate'),
            'translate_confirm_leave' => $this->translator->trans('translate.confirm.leave', domain: 'translate'),
            'translate_toast_title_success' =>
                $this->translator->trans('translate.toast.title.success', domain: 'translate'),
            'translate_toast_title_error' =>
                $this->translator->trans('translate.toast.title.error', domain: 'translate'),
            'translate_toast_time' =>
                $this->translator->trans('translate.toast.time', domain: 'translate'),
        ];
    }
}
