<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour apiToken
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Translate\System;

use App\Utils\Translate\AppTranslate;

class ApiTokenTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour les ApiToken
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'toast_title_success' => $this->translator->trans('api_token.toast.title.success', domain: 'api_token'),
            'toast_title_error' => $this->translator->trans('api_token.toast.title.error', domain: 'api_token'),
            'toast_time' => $this->translator->trans('api_token.toast.time', domain: 'api_token'),
            'title_add' => $this->translator->trans('api_token.card.title.add', domain: 'api_token'),
            'title_edit' => $this->translator->trans('api_token.card.title.edit', domain: 'api_token'),
            'btn_new_token' => $this->translator->trans('api_token.card.btn.new_token', domain: 'api_token'),
            'generate_token_success' => $this->translator->trans('api_token.card.generate.token.success', domain: 'api_token'),
            'btn_copy_past' => $this->translator->trans('api_token.card.btn.copy_past', domain: 'api_token'),
            "input_token_help" => $this->translator->trans('api_token.card.input.token.help', domain: 'api_token'),
            "select_label_role" => $this->translator->trans('api_token.card.select.label.role', domain: 'api_token'),
        ];
    }
}
