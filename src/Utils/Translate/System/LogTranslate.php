<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour Log
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Translate\System;

use App\Utils\Translate\AppTranslate;

class LogTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour les logs
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'log_block_search_title' => $this->translator->trans('log.block.search.title', domain: 'log'),
            'log_block_search_sub_title' => $this->translator->trans('log.block.search.sub.title', domain: 'log'),
            'log_select_file_label' => $this->translator->trans('log.select-file.label', domain: 'log'),
            'log_select_file' => $this->translator->trans('log.select-file', domain: 'log'),
            'log_select_time_label' => $this->translator->trans('log.select-time.label', domain: 'log'),
            'log_select_time_all' => $this->translator->trans('log.select-time.all', domain: 'log'),
            'log_select_time_now' => $this->translator->trans('log.select-time.now', domain: 'log'),
            'log_select_time_yesterday' => $this->translator->trans('log.select-time.yesterday', domain: 'log'),
            'log_file' => $this->translator->trans('log.file', domain: 'log'),
            'log_file_size' => $this->translator->trans('log.file.size', domain: 'log'),
            'log_file_ligne' => $this->translator->trans('log.file.ligne', domain: 'log'),
            'log_btn_delete_file' => $this->translator->trans('log.btn.delete.file', domain: 'log'),
            'log_btn_download_file' => $this->translator->trans('log.btn.download.file', domain: 'log'),
            'log_empty_file' => $this->translator->trans('log.empty.file', domain: 'log'),
            'log_delete_file_confirm' => $this->translator->trans('log.delete.file.confirm', domain: 'log'),
            'log_delete_file_confirm_2' => $this->translator->trans('log.delete.file.confirm_2', domain: 'log'),
            'log_delete_file_loading' => $this->translator->trans('log.delete.file.loading', domain: 'log'),
            'log_delete_file_btn_close' => $this->translator->trans('log.delete.file.btn_close', domain: 'log'),
            'log_btn_reload' => $this->translator->trans('log.btn.reload', domain: 'log'),
            'toast_title_success' => $this->translator->trans('log.toast.title.success', domain: 'log'),
            'toast_time' => $this->translator->trans('log.toast.time', domain: 'log'),
            'toast_title_error' => $this->translator->trans('log.toast.title.error', domain: 'log'),
        ];
    }
}
