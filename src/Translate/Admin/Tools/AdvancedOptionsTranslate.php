<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue de AvancedOptions
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Translate\Admin\Tools;

use App\Translate\AppTranslate;

class AdvancedOptionsTranslate extends AppTranslate
{
    /**
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'switch_env_title' => $this->translator->trans(
                'advanced_options.switch.env.title',
                domain: 'advanced_options',
            ),
            'switch_env_subtitle_prod' => $this->translator->trans(
                'advanced_options.switch.subtitle.env.prod',
                domain: 'advanced_options',
            ),
            'switch_env_subtitle_dev' => $this->translator->trans(
                'advanced_options.switch.subtitle.env.dev',
                domain: 'advanced_options',
            ),
            'switch_env_define_dev' => $this->translator->trans(
                'advanced_options.switch.define.env.dev',
                domain: 'advanced_options',
            ),
            'switch_env_define_dev_1' => $this->translator->trans(
                'advanced_options.switch.define.env.dev_1',
                domain: 'advanced_options',
            ),
            'switch_env_define_dev_2' => $this->translator->trans(
                'advanced_options.switch.define.env.dev_2',
                domain: 'advanced_options',
            ),
            'switch_env_define_dev_3' => $this->translator->trans(
                'advanced_options.switch.define.env.dev_3',
                domain: 'advanced_options',
            ),
            'switch_env_define_dev_4' => $this->translator->trans(
                'advanced_options.switch.define.env.dev_4',
                domain: 'advanced_options',
            ),
            'switch_env_define_dev_warning' => $this->translator->trans(
                'advanced_options.switch.define.env.dev_warning',
                domain: 'advanced_options',
            ),
            'switch_env_define_prod' => $this->translator->trans(
                'advanced_options.switch.define.env.prod',
                domain: 'advanced_options',
            ),
            'switch_env_define_prod_1' => $this->translator->trans(
                'advanced_options.switch.define.env.prod_1',
                domain: 'advanced_options',
            ),
            'switch_env_define_prod_2' => $this->translator->trans(
                'advanced_options.switch.define.env.prod_2',
                domain: 'advanced_options',
            ),
            'switch_env_define_prod_3' => $this->translator->trans(
                'advanced_options.switch.define.env.prod_3',
                domain: 'advanced_options',
            ),
            'switch_env_define_prod_warning' => $this->translator->trans(
                'advanced_options.switch.define.env.prod_warning',
                domain: 'advanced_options',
            ),
            'switch_env_btn_dev' => $this->translator->trans(
                'advanced_options.switch.env.button.dev',
                domain: 'advanced_options',
            ),
            'switch_env_btn_prod' => $this->translator->trans(
                'advanced_options.switch.env.button.prod',
                domain: 'advanced_options',
            ),
            'title_danger_zone' => $this->translator->trans(
                'advanced_options.title.danger.zone',
                domain: 'advanced_options',
            ),
            'subtitle_danger_zone' => $this->translator->trans(
                'advanced_options.sub.title.danger.zone',
                domain: 'advanced_options',
            ),
            'warning_danger_zone' => $this->translator->trans(
                'advanced_options.warning.danger.zone',
                domain: 'advanced_options',
            ),
            'error_generic' => $this->translator->trans('advanced_options.error.generic', domain: 'advanced_options'),
            'confirm_modale_env' => $this->getModaleConfirmSwitchEnvTranslate(),
            'confirm_modale_reset_database' => $this->getModaleConfirmResetDatabaseTranslate(),
            'confirm_modale_reset_data' => $this->getModaleConfirmResetDataTranslate(),
            'msg_info' => $this->getMsgInfoTranslate(),
            'reload_data' => $this->getBlocReloadDataTranslate(),
            'reset_database' => $this->getBlocResetDatabaseTranslate(),
        ];
    }

    /**
     * Retourne les traductions du bloc reset database
     * @return array
     */
    private function getBlocResetDatabaseTranslate(): array
    {
        return [
            'title' => $this->translator->trans('advanced_options.reset.database.title', domain: 'advanced_options'),
            'text_1' => $this->translator->trans('advanced_options.reset.database.text_1', domain: 'advanced_options'),
            'warning' => $this->translator->trans(
                'advanced_options.reset.database.warning',
                domain: 'advanced_options',
            ),
            'btn' => $this->translator->trans('advanced_options.reset.database.button', domain: 'advanced_options'),
        ];
    }

    /**
     * Retourne les traductions du bloc reload data
     * @return array
     */
    private function getBlocReloadDataTranslate(): array
    {
        return [
            'title' => $this->translator->trans('advanced_options.reload.data.title', domain: 'advanced_options'),
            'text_1' => $this->translator->trans('advanced_options.reload.data.text_1', domain: 'advanced_options'),
            'warning' => $this->translator->trans('advanced_options.reload.data.warning', domain: 'advanced_options'),
            'btn' => $this->translator->trans('advanced_options.reload.data.button', domain: 'advanced_options'),
        ];
    }

    /**
     * Traduction de la modale de confirmation de switch d'environnement
     * @return array
     */
    private function getModaleConfirmSwitchEnvTranslate(): array
    {
        return [
            'modale_title' => $this->translator->trans(
                'advanced_options.modal.confirm.env.title',
                domain: 'advanced_options',
            ),
            'modale_body_text_1' => $this->translator->trans(
                'advanced_options.modal.confirm.env.body.text.1',
                domain: 'advanced_options',
            ),
            'modale_body_text_2' => $this->translator->trans(
                'advanced_options.modal.confirm.env.body.text.2',
                domain: 'advanced_options',
            ),
            'modale_btn_confirm' => $this->translator->trans(
                'advanced_options.modal.confirm.env.button.confirm',
                domain: 'advanced_options',
            ),
            'modale_btn_undo' => $this->translator->trans(
                'advanced_options.modal.confirm.env.button.undo',
                domain: 'advanced_options',
            ),
        ];
    }

    /**
     * Traduction de la modale de confirmation de switch d'environnement
     * @return array
     */
    private function getModaleConfirmResetDatabaseTranslate(): array
    {
        return [
            'modale_title' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.database.title',
                domain: 'advanced_options',
            ),
            'modale_body_text_1' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.database.body.text.1',
                domain: 'advanced_options',
            ),
            'modale_body_text_2' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.database.body.text.2',
                domain: 'advanced_options',
            ),
            'modale_btn_confirm' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.database.button.confirm',
                domain: 'advanced_options',
            ),
            'modale_btn_undo' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.database.button.undo',
                domain: 'advanced_options',
            ),
        ];
    }

    /**
     * Traduction de la modale de confirmation de switch d'environnement
     * @return array
     */
    private function getModaleConfirmResetDataTranslate(): array
    {
        return [
            'modale_title' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.data.title',
                domain: 'advanced_options',
            ),
            'modale_body_text_1' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.data.body.text.1',
                domain: 'advanced_options',
            ),
            'modale_body_text_2' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.data.body.text.2',
                domain: 'advanced_options',
            ),
            'modale_btn_confirm' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.data.button.confirm',
                domain: 'advanced_options',
            ),
            'modale_btn_undo' => $this->translator->trans(
                'advanced_options.modal.confirm.reset.data.button.undo',
                domain: 'advanced_options',
            ),
        ];
    }

    /**
     * Traduction des messages d'informations
     * @return array
     */
    private function getMsgInfoTranslate(): array
    {
        return [
            'title' => $this->translator->trans(
                'advanced_options.switch.env.message.info.title',
                domain: 'advanced_options',
            ),
            'switch_env' => $this->translator->trans(
                'advanced_options.switch.env.message.info.switch',
                domain: 'advanced_options',
            ),
            'switch_env_end' => $this->translator->trans(
                'advanced_options.switch.env.message.info.switch_end',
                domain: 'advanced_options',
            ),
            'reset_data' => $this->translator->trans(
                'advanced_options.reset.data.message.info.reset',
                domain: 'advanced_options',
            ),
            'reset_data_end' => $this->translator->trans(
                'advanced_options.reset.data.message.info.reset_end',
                domain: 'advanced_options',
            ),
            'reset_database' => $this->translator->trans(
                'advanced_options.reset.database.message.info.reset',
                domain: 'advanced_options',
            ),
            'reset_database_end' => $this->translator->trans(
                'advanced_options.reset.database.message.info.reset_end',
                domain: 'advanced_options',
            ),
        ];
    }
}
