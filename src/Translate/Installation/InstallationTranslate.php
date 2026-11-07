<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traduction pour l'installation
 */

namespace App\Translate\Installation;

use App\Translate\AppTranslate;

class InstallationTranslate extends AppTranslate
{
    /**
     * Traduction étape 1
     * @return array
     */
    public function getTranslateStepOne(): array
    {
        return [
            'loading' => $this->translator->trans('installation.global.loading', domain: 'installation'),
            'title' => $this->translator->trans('installation.step_1.title', domain: 'installation'),
            'title_h1' => $this->translator->trans('installation.step_1.title_h1', domain: 'installation'),
            'config_bdd_title' => $this->translator->trans(
                'installation.step_1.config.database.title',
                domain: 'installation',
            ),
            'config_bdd_input_type_label' => $this->translator->trans(
                'installation.step_1.config.database.input.type.label',
                domain: 'installation',
            ),
            'config_bdd_input_login_label' => $this->translator->trans(
                'installation.step_1.config.database.input.login.label',
                domain: 'installation',
            ),
            'config_bdd_input_password_label' => $this->translator->trans(
                'installation.step_1.config.database.input.password.label',
                domain: 'installation',
            ),
            'config_bdd_input_ip_label' => $this->translator->trans(
                'installation.step_1.config.database.input.ip.label',
                domain: 'installation',
            ),
            'config_bdd_input_port_label' => $this->translator->trans(
                'installation.step_1.config.database.input.port.label',
                domain: 'installation',
            ),
            'config_bdd_btn_test_config' => $this->translator->trans(
                'installation.step_1.config.database.button.test.config',
                domain: 'installation',
            ),
            'config_bdd_btn_save_config' => $this->translator->trans(
                'installation.step_1.config.database.button.save.config',
                domain: 'installation',
            ),
            'config_bdd_btn_test_config_loading' => $this->translator->trans(
                'installation.step_1.config.database.button.test.config_loading',
                domain: 'installation',
            ),
            'config_bdd_loading_msg_update_file' => $this->translator->trans(
                'installation.step_1.config.database.loading.message.update.file',
                domain: 'installation',
            ),
            'config_bdd_loading_msg_test_connexion_success' => $this->translator->trans(
                'installation.step_1.config.database.loading.message.test.connection.success',
                domain: 'installation',
            ),
            'create_bdd_h1' => $this->translator->trans('installation.step_1.create.database.h1', domain: 'installation'),
            'create_bdd_description_1' => $this->translator->trans(
                'installation.step_1.create.database.description.1',
                domain: 'installation',
            ),
            'create_bdd_description_2' => $this->translator->trans(
                'installation.step_1.create.database.description.2',
                domain: 'installation',
            ),
            'create_bdd_alert_title' => $this->translator->trans(
                'installation.step_1.create.database.alert.title',
                domain: 'installation',
            ),
            'create_bdd_alert_text_1' => $this->translator->trans(
                'installation.step_1.create.database.alert.text.1',
                domain: 'installation',
            ),
            'create_bdd_alert_text_2' => $this->translator->trans(
                'installation.step_1.create.database.alert.text.2',
                domain: 'installation',
            ),
            'create_bdd_alert_text_3' => $this->translator->trans(
                'installation.step_1.create.database.alert.text.3',
                domain: 'installation',
            ),
            'create_bdd_alert_text_4' => $this->translator->trans(
                'installation.step_1.create.database.alert.text.4',
                domain: 'installation',
            ),
            'create_bdd_alert_text_5' => $this->translator->trans(
                'installation.step_1.create.database.alert.text.5',
                domain: 'installation',
            ),
            'create_bdd_title' => $this->translator->trans(
                'installation.step_1.create.database.title',
                domain: 'installation',
            ),
            'create_bdd_input_bdd_name_label' => $this->translator->trans(
                'installation.step_1.create.database.input.database.name.label',
                domain: 'installation',
            ),
            'create_bdd_input_version_label' => $this->translator->trans(
                'installation.step_1.create.database.input.version.label',
                domain: 'installation',
            ),
            'create_bdd_input_charset_label' => $this->translator->trans(
                'installation.step_1.create.database.input.charset.label',
                domain: 'installation',
            ),
            'create_bdd_btn_create' => $this->translator->trans(
                'installation.step_1.create.database.button.create',
                domain: 'installation',
            ),
            'create_bdd_loading_msg_update_file' => $this->translator->trans(
                'installation.step_1.create.database.message.update.file',
                domain: 'installation',
            ),
            'create_bdd_loading_msg_update_secret_success' => $this->translator->trans(
                'installation.step_1.create.database.message.update.secret_success',
                domain: 'installation',
            ),
            'create_bdd_loading_msg_update_secret_ko' => $this->translator->trans(
                'installation.step_1.create.database.message.update.secret_ko',
                domain: 'installation',
            ),
            'create_bdd_loading_msg_create_bdd_success' => $this->translator->trans(
                'installation.step_1.create.database.message.create.database.success',
                domain: 'installation',
            ),
            'create_bdd_loading_msg_create_bdd_ko' => $this->translator->trans(
                'installation.step_1.create.database.message.create.database.ko',
                domain: 'installation',
            ),
            'create_bdd_loading_msg_create_table_success' => $this->translator->trans(
                'installation.step_1.create.database.message.create.table.success',
                domain: 'installation',
            ),
            'create_bdd_loading_msg_create_table_ko' => $this->translator->trans(
                'installation.step_1.create.database.message.create.table.ko',
                domain: 'installation',
            ),
            'create_bdd_loading_msg_success' => $this->translator->trans(
                'installation.step_1.create.database.message.success',
                domain: 'installation',
            ),
            'field_required' => $this->translator->trans('installation.step_1.field.required', domain: 'installation'),
            'btn_return' => $this->translator->trans('installation.step_1.button.return', domain: 'installation'),
            'create_bdd_sub_title_identifiant' => $this->translator->trans(
                'installation.step_1.sub_title.identifier',
                domain: 'installation',
            ),
            'create_bdd_sub_title_server' => $this->translator->trans(
                'installation.step_1.sub_title.server',
                domain: 'installation',
            ),
            'create_bdd_sub_title_options' => $this->translator->trans(
                'installation.step_1.sub_title.options',
                domain: 'installation',
            ),
            'btn_step_two' => $this->translator->trans('installation.step_1.button.step.2', domain: 'installation'),
            'create_bdd_msg_already_exist' => $this->translator->trans(
                'installation.step_1.message.database.already_exist',
                domain: 'installation',
            ),
        ];
    }

    /**
     * Traduction étape 2
     * @return array
     */
    public function getTranslateStepTwo(): array
    {
        return [
            'loading' => $this->translator->trans('installation.global.loading', domain: 'installation'),
            'title' => $this->translator->trans('installation.step_2.title', domain: 'installation'),
            'title_h1' => $this->translator->trans('installation.step_2.title_h1', domain: 'installation'),
            'fondateur_description' => $this->translator->trans(
                'installation.step_2.founder.description',
                domain: 'installation',
            ),
            'fondateur_titre_card' => $this->translator->trans(
                'installation.step_2.founder.title.card',
                domain: 'installation',
            ),
            'fondateur_login_label' => $this->translator->trans(
                'installation.step_2.founder.login.label',
                domain: 'installation',
            ),
            'fondateur_login_placeholder' => $this->translator->trans(
                'installation.step_2.founder.login.placeholder',
                domain: 'installation',
            ),
            'fondateur_login_error' => $this->translator->trans(
                'installation.step_2.founder.login.error',
                domain: 'installation',
            ),
            'fondateur_email_label' => $this->translator->trans(
                'installation.step_2.founder.email.label',
                domain: 'installation',
            ),
            'fondateur_email_placeholder' => $this->translator->trans(
                'installation.step_2.founder.email.placeholder',
                domain: 'installation',
            ),
            'fondateur_email_error' => $this->translator->trans(
                'installation.step_2.founder.email.error',
                domain: 'installation',
            ),
            'fondateur_password_label' => $this->translator->trans(
                'installation.step_2.founder.password.label',
                domain: 'installation',
            ),
            'fondateur_password_error' => $this->translator->trans(
                'installation.step_2.founder.password.error',
                domain: 'installation',
            ),
            'fondateur_password_weak' => $this->translator->trans(
                'installation.step_2.founder.password.weak',
                domain: 'installation',
            ),
            'fondateur_password_normal' => $this->translator->trans(
                'installation.step_2.founder.password.normal',
                domain: 'installation',
            ),
            'fondateur_password_strong' => $this->translator->trans(
                'installation.step_2.founder.password.strong',
                domain: 'installation',
            ),
            'fondateur_password_help' => $this->translator->trans(
                'installation.step_2.founder.password.help',
                domain: 'installation',
            ),
            'fondateur_btn_create' => $this->translator->trans(
                'installation.step_2.founder.button.create',
                domain: 'installation',
            ),
            'fondateur_loading_msg' => $this->translator->trans(
                'installation.step_2.founder.loading.message',
                domain: 'installation',
            ),
            'fondateur_success' => $this->translator->trans(
                'installation.step_2.founder.success',
                domain: 'installation',
            ),
            'debug_titre' => $this->translator->trans('installation.step_2.debug.title', domain: 'installation'),
            'debug_texte_1' => $this->translator->trans('installation.step_2.debug.text.1', domain: 'installation'),
            'debug_texte_2' => $this->translator->trans('installation.step_2.debug.text.2', domain: 'installation'),
            'debug_texte_3' => $this->translator->trans('installation.step_2.debug.text.3', domain: 'installation'),
            'debug_texte_4' => $this->translator->trans('installation.step_2.debug.text.4', domain: 'installation'),
            'debug_texte_5' => $this->translator->trans('installation.step_2.debug.text.5', domain: 'installation'),
            'debug_texte_6' => $this->translator->trans('installation.step_2.debug.text.6', domain: 'installation'),
            'finish_installation_title' => $this->translator->trans(
                'installation.step_2.finish.title',
                domain: 'installation',
            ),
            'finish_installation_text_1' => $this->translator->trans(
                'installation.step_2.finish.text.1',
                domain: 'installation',
            ),
            'finish_installation_text_2' => $this->translator->trans(
                'installation.step_2.finish.text.2',
                domain: 'installation',
            ),
            'finish_installation_btn' => $this->translator->trans(
                'installation.step_2.finish.button',
                domain: 'installation',
            ),
            'finish_installation_loading_msg_success' => $this->translator->trans(
                'installation.step_2.finish.loading.message.success',
                domain: 'installation',
            ),
            'finish_installation_loading_msg_config_success' => $this->translator->trans(
                'installation.step_2.finish.loading.message.config.success',
                domain: 'installation',
            ),
            'finish_installation_loading_msg_config_ko' => $this->translator->trans(
                'installation.step_2.finish.loading.message.config.ko',
                domain: 'installation',
            ),
            'finish_installation_loading_msg_installation_data_success' => $this->translator->trans(
                'installation.step_2.finish.loading.message.installation.data.success',
                domain: 'installation',
            ),
            'finish_installation_loading_msg_installation_data_ko' => $this->translator->trans(
                'installation.step_2.finish.loading.message.installation.data.ko',
                domain: 'installation',
            ),
            'finish_installation_loading_msg_cache_success' => $this->translator->trans(
                'installation.step_2.finish.loading.message.cache.success',
                domain: 'installation',
            ),
            'finish_installation_loading_msg_cache_ko' => $this->translator->trans(
                'installation.step_2.finish.loading.message.cache.ko',
                domain: 'installation',
            ),
        ];
    }

    public function getTranslateStepFour(): array
    {
        return [
            'title_h1' => $this->translator->trans('installation.step_4.title', domain: 'installation'),
            'description' => $this->translator->trans('installation.step_4.description', domain: 'installation'),
            'summary_database' => $this->translator->trans(
                'installation.step_4.summary.database',
                domain: 'installation',
            ),
            'summary_user' => $this->translator->trans('installation.step_4.summary.user', domain: 'installation'),
            'summary_data' => $this->translator->trans('installation.step_4.summary.data', domain: 'installation'),
            'summary_cache' => $this->translator->trans('installation.step_4.summary.cache', domain: 'installation'),
            'btn_login' => $this->translator->trans('installation.step_4.button.login', domain: 'installation'),
        ];
    }
}
