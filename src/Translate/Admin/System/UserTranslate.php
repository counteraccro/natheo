<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue pour User
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Translate\Admin\System;

use App\Translate\AppTranslate;

class UserTranslate extends AppTranslate
{
    /**
     * Renvoi les traductions pour le changement de mot de passe
     * @return array
     */
    public function getTranslateChangePassword(): array
    {
        return [
            'password_current' => $this->translator->trans(
                'user.change_password.input.password_current',
                domain: 'user',
            ),
            'password' => $this->translator->trans('user.change_password.input.password_1', domain: 'user'),
            'password_2' => $this->translator->trans('user.change_password.input.password_2', domain: 'user'),
            'force' => $this->translator->trans('user.change_password.force', domain: 'user'),
            'btn_submit' => $this->translator->trans('user.change_password.button.submit', domain: 'user'),
            'force_nb_character' => $this->translator->trans('user.change_password.force_nb_character', domain: 'user'),
            'force_majuscule' => $this->translator->trans('user.change_password.force_uppercase', domain: 'user'),
            'force_minuscule' => $this->translator->trans('user.change_password.force_lowercase', domain: 'user'),
            'force_chiffre' => $this->translator->trans('user.change_password.force_digit', domain: 'user'),
            'force_character_spe' => $this->translator->trans(
                'user.change_password.force_character_special',
                domain: 'user',
            ),
            'error_password_2' => $this->translator->trans('user.error.password_2', domain: 'user'),
        ];
    }

    /**
     * Renvoi les traductions pour la danger-zone de mon profil
     * @return array
     */
    public function getTranslateDangerZone(): array
    {
        return [
            'loading' => $this->translator->trans('user.danger_zone.loading', domain: 'user'),
            'btn_disabled' => $this->translator->trans('user.danger_zone.button_disabled', domain: 'user'),
            'btn_delete' => $this->translator->trans('user.danger_zone.button_delete', domain: 'user'),
            'btn_replace' => $this->translator->trans('user.danger_zone.button_replace', domain: 'user'),
            'disabled_title' => $this->translator->trans('user.danger_zone.title_disabled', domain: 'user'),
            'disabled_description' => $this->translator->trans('user.danger_zone.description_disabled', domain: 'user'),
            'disabled_content_1' => $this->translator->trans('user.danger_zone.content_disabled_1', domain: 'user'),
            'disabled_content_2' => $this->translator->trans('user.danger_zone.content_disabled_2', domain: 'user'),
            'disabled_btn_cancel' => $this->translator->trans('user.danger_zone.button_cancel_disabled', domain: 'user'),
            'disabled_btn_confirm' => $this->translator->trans('user.danger_zone.button_confirm_disabled', domain: 'user'),
            'delete_btn_cancel' => $this->translator->trans('user.danger_zone.button_cancel_delete', domain: 'user'),
            'delete_btn_confirm' => $this->translator->trans('user.danger_zone.button_confirm_delete', domain: 'user'),
            'delete_title' => $this->translator->trans('user.danger_zone.title_delete', domain: 'user'),
            'delete_description' => $this->translator->trans('user.danger_zone.description_delete', domain: 'user'),
            'delete_content_1' => $this->translator->trans('user.danger_zone.content_delete_1', domain: 'user'),
            'delete_content_2' => $this->translator->trans('user.danger_zone.content_delete_2', domain: 'user'),
            'replace_btn_cancel' => $this->translator->trans('user.danger_zone.button_cancel_replace', domain: 'user'),
            'replace_btn_confirm' => $this->translator->trans('user.danger_zone.button_confirm_replace', domain: 'user'),
            'replace_title' => $this->translator->trans('user.danger_zone.title_replace', domain: 'user'),
            'replace_description' => $this->translator->trans('user.danger_zone.description_replace', domain: 'user'),
            'replace_content_1' => $this->translator->trans('user.danger_zone.content_replace_1', domain: 'user'),
            'replace_content_2' => $this->translator->trans('user.danger_zone.content_replace_2', domain: 'user'),
        ];
    }

    /**
     * Retourne les traductions du block more options
     * @return array
     */
    public function getTranslateMoreOptions()
    {
        return [
            'loading' => $this->translator->trans('user.more_options.loading', domain: 'user'),
            'btn_show_help' => $this->translator->trans('user.more_options.button.show_help', domain: 'user'),
            'btn_hide_help' => $this->translator->trans('user.more_options.button.hide_help', domain: 'user'),
        ];
    }
}
