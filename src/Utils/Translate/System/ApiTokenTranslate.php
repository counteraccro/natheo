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
            'loading' => $this->translator->trans('api_token.loading', domain: 'api_token'),
            'title_add' => $this->translator->trans('api_token.card.title.add', domain: 'api_token'),
            'title_edit' => $this->translator->trans('api_token.card.title.edit', domain: 'api_token'),
            'description_add' => $this->translator->trans('api_token.card.description.add', domain: 'api_token'),
            'description_edit' => $this->translator->trans('api_token.card.description.edit', domain: 'api_token'),
            'btn_new_token' => $this->translator->trans('api_token.card.btn.new_token', domain: 'api_token'),
            'generate_token_success' => $this->translator->trans(
                'api_token.card.generate.token.success',
                domain: 'api_token',
            ),
            'btn_copy_past' => $this->translator->trans('api_token.card.btn.copy_past', domain: 'api_token'),
            'title_label' => $this->translator->trans('api_token.card.title.label', domain: 'api_token'),
            'name_error' => $this->translator->trans('api_token.card.title.error', domain: 'api_token'),
            'title_help' => $this->translator->trans('api_token.card.title.help', domain: 'api_token'),
            'title_placeholder' => $this->translator->trans('api_token.card.title.placeholder', domain: 'api_token'),
            'comment_label' => $this->translator->trans('api_token.card.comment.label', domain: 'api_token'),
            'comment_help' => $this->translator->trans('api_token.card.comment.help', domain: 'api_token'),
            'comment_placeholder' => $this->translator->trans(
                'api_token.card.comment.placeholder',
                domain: 'api_token',
            ),
            'token_label' => $this->translator->trans('api_token.card.token.label', domain: 'api_token'),
            'token_copy_success' => $this->translator->trans('api_token.card.copy.success', domain: 'api_token'),
            'input_token_help' => $this->translator->trans('api_token.card.input.token.help', domain: 'api_token'),
            'input_token_help_add' => $this->translator->trans(
                'api_token.card.input.token.help.add',
                domain: 'api_token',
            ),
            'input_token_error' => $this->translator->trans('api_token.card.input.token.error', domain: 'api_token'),
            'select_label_role' => $this->translator->trans('api_token.card.select.label.role', domain: 'api_token'),
            'help_role' => $this->translator->trans('api_token.card.help.role', domain: 'api_token'),
            'help_role_read' => $this->translator->trans('api_token.card.help.role_read', domain: 'api_token'),
            'help_role_write' => $this->translator->trans('api_token.card.help.role_write', domain: 'api_token'),
            'help_role_admin' => $this->translator->trans('api_token.card.help.role_admin', domain: 'api_token'),
            'btn_edit_token_api' => $this->translator->trans('api_token.card.btn.edit.token.api', domain: 'api_token'),
            'btn_save_token_api' => $this->translator->trans('api_token.card.btn.save.token.api', domain: 'api_token'),
            'modale_title_confirm_edit' => $this->translator->trans(
                'api_token.modale.confirm.edit.title',
                domain: 'api_token',
            ),
            'modale_title_confirm_text' => $this->translator->trans(
                'api_token.modale.confirm.edit.text',
                domain: 'api_token',
            ),
            'modale_title_confirm_btn_ok' => $this->translator->trans(
                'api_token.modale.confirm.edit.btn_ok',
                domain: 'api_token',
            ),
            'modale_title_confirm_btn_ko' => $this->translator->trans(
                'api_token.modale.confirm.edit.btn_ko',
                domain: 'api_token',
            ),
        ];
    }
}
