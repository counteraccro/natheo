<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traduction pour l'installation
 */
namespace App\Utils\Translate\Installation;

use App\Utils\Translate\AppTranslate;

class InstallationTranslate extends AppTranslate
{
    /**
     * Traduction étape 1
     * @return array
     */
    public function getTranslateStepOne(): array
    {
        return [
            'loading' => $this->translator->trans('installation.loading', domain: 'installation'),
            'title' => $this->translator->trans('installation.step.1.titre', domain:'installation'),
            'title_h1' => $this->translator->trans('installation.step.1.titre.h1', domain:'installation'),
            'description_1' => $this->translator->trans('installation.step.1.description_1', domain:'installation'),
            'description_2' => $this->translator->trans('installation.step.1.description_2', domain:'installation'),
            'config_bdd_title' => $this->translator->trans('installation.step.1.config.bdd.title', domain:'installation'),
            'config_bdd_input_type_label' => $this->translator->trans('installation.step.1.config.bdd.input.type.label', domain:'installation'),
            'config_bdd_input_login_label' => $this->translator->trans('installation.step.1.config.bdd.input.login.label', domain:'installation'),
            'config_bdd_input_password_label' => $this->translator->trans('installation.step.1.config.bdd.input.password.label', domain:'installation'),
            'config_bdd_input_ip_label' => $this->translator->trans('installation.step.1.config.bdd.input.ip.label', domain:'installation'),
            'config_bdd_input_port_label' => $this->translator->trans('installation.step.1.config.bdd.input.port.label', domain:'installation'),
            'config_bdd_btn_test_config' => $this->translator->trans('installation.step.1.config.bdd.btn.test.config', domain:'installation'),
            'config_bdd_btn_test_config_loading' => $this->translator->trans('installation.step.1.config.bdd.btn.test.config.loading', domain:'installation'),
            'title_thx_h1' => $this->translator->trans('installation.step.1.titre.thx.h1', domain:'installation'),
            'description_thx_1' => $this->translator->trans('installation.step.1.description.thx.1', domain:'installation'),
            'config_bdd_loading_msg_update_file' => $this->translator->trans('installation.step.1.config.bdd.loading.msg.update.file', domain: 'installation'),
            'config_bdd_loading_msg_test_connexion' => $this->translator->trans('installation.step.1.config.bdd.loading.msg.test.connexion', domain: 'installation'),
            'config_bdd_loading_msg_test_connexion_ko' => $this->translator->trans('installation.step.1.config.bdd.loading.msg.test.connexion.ko', domain: 'installation'),
            'config_bdd_loading_msg_test_connexion_success' => $this->translator->trans('installation.step.1.config.bdd.loading.msg.test.connexion.success', domain: 'installation'),
            'create_bdd_h1' => $this->translator->trans('installation.step.1.create.bdd.h1', domain:'installation'),
            'create_bdd_description_1' => $this->translator->trans('installation.step.1.create.bdd.description.1', domain:'installation'),
            'create_bdd_description_2' => $this->translator->trans('installation.step.1.create.bdd.description.2', domain:'installation'),
            'create_bdd_alert_title' => $this->translator->trans('installation.step.1.create.bdd.alert.title', domain:'installation'),
            'create_bdd_alert_text_1' => $this->translator->trans('installation.step.1.create.bdd.alert.text.1', domain:'installation'),
            'create_bdd_alert_text_2' => $this->translator->trans('installation.step.1.create.bdd.alert.text.2', domain:'installation'),
            'create_bdd_alert_text_3' => $this->translator->trans('installation.step.1.create.bdd.alert.text.3', domain:'installation'),
            'create_bdd_alert_text_4' => $this->translator->trans('installation.step.1.create.bdd.alert.text.4', domain:'installation'),
            'create_bdd_alert_text_5' => $this->translator->trans('installation.step.1.create.bdd.alert.text.5', domain:'installation'),
            'create_bdd_title' => $this->translator->trans('installation.step.1.create.bdd.title', domain:'installation'),
            'create_bdd_input_bdd_name_label' => $this->translator->trans('installation.step.1.create.bdd.input.bdd.name.label', domain:'installation'),
            'create_bdd_input_version_label' => $this->translator->trans('installation.step.1.create.bdd.input.version.label', domain:'installation'),
            'create_bdd_input_charset_label' => $this->translator->trans('installation.step.1.create.bdd.input.charset.label', domain:'installation'),
            'create_bdd_input_bdd_name_error' => $this->translator->trans('installation.step.1.create.bdd.input.bdd.name.error', domain:'installation'),
            'create_bdd_input_version_error' => $this->translator->trans('installation.step.1.create.bdd.input.version.error', domain:'installation'),
            'create_bdd_btn_create' => $this->translator->trans('installation.step.1.create.bdd.btn.create', domain:'installation'),
            'create_bdd_loading_msg_update_file' => $this->translator->trans('installation.step.1.create.bdd.msg.update.file', domain:'installation'),
            'create_bdd_loading_msg_update_secret' => $this->translator->trans('installation.step.1.create.bdd.msg.update.secret', domain:'installation'),
            'create_bdd_loading_msg_update_secret_success' => $this->translator->trans('installation.step.1.create.bdd.msg.update.secret_success', domain:'installation'),
            'create_bdd_loading_msg_update_secret_ko' => $this->translator->trans('installation.step.1.create.bdd.msg.update.secret_ko', domain:'installation'),
            'create_bdd_loading_msg_create_bdd' => $this->translator->trans('installation.step.1.create.bdd.msg.create.bdd', domain:'installation'),
            'create_bdd_loading_msg_create_bdd_success' => $this->translator->trans('installation.step.1.create.bdd.msg.create.bdd.success', domain:'installation'),
            'create_bdd_loading_msg_create_bdd_ko' => $this->translator->trans('installation.step.1.create.bdd.msg.create.bdd.ko', domain:'installation'),
            'create_bdd_loading_msg_create_table' => $this->translator->trans('installation.step.1.create.bdd.msg.create.table', domain:'installation'),
            'create_bdd_loading_msg_create_table_success' => $this->translator->trans('installation.step.1.create.bdd.msg.create.table.success', domain:'installation'),
            'create_bdd_loading_msg_create_table_ko' => $this->translator->trans('installation.step.1.create.bdd.msg.create.table.ko', domain:'installation'),
            'create_bdd_loading_msg_success' => $this->translator->trans('installation.step.1.create.bdd.msg.success', domain:'installation'),

        ];
    }

    /**
     * Traduction étape 2
     * @return array
     */
    public function getTranslateStepTwo(): array
    {
        return [
            'loading' => $this->translator->trans('installation.loading', domain: 'installation'),
            'title' => $this->translator->trans('installation.step.2.titre', domain:'installation'),
            'title_h1' => $this->translator->trans('installation.step.2.titre.h1', domain:'installation'),
            'fondateur_description' => $this->translator->trans('installation.step.2.fondateur.description', domain:'installation'),
            'fondateur_titre_card' => $this->translator->trans('installation.step.2.fondateur.titre.card', domain:'installation'),
            'fondateur_login_label' => $this->translator->trans('installation.step.2.fondateur.login.label', domain:'installation'),
            'fondateur_login_placeholder' => $this->translator->trans('installation.step.2.fondateur.login.placeholder', domain:'installation'),
            'fondateur_login_error' => $this->translator->trans('installation.step.2.fondateur.login.error', domain:'installation'),
            'fondateur_email_label' => $this->translator->trans('installation.step.2.fondateur.email.label', domain:'installation'),
            'fondateur_email_placeholder' => $this->translator->trans('installation.step.2.fondateur.email.placeholder', domain:'installation'),
            'fondateur_email_error' => $this->translator->trans('installation.step.2.fondateur.email.error', domain:'installation'),
            'fondateur_password_label' => $this->translator->trans('installation.step.2.fondateur.password.label', domain:'installation'),
            'fondateur_password_error' => $this->translator->trans('installation.step.2.fondateur.password.error', domain:'installation'),
            'fondateur_password_weak' => $this->translator->trans('installation.step.2.fondateur.password.weak', domain:'installation'),
            'fondateur_password_normal' => $this->translator->trans('installation.step.2.fondateur.password.normal', domain:'installation'),
            'fondateur_password_strong' => $this->translator->trans('installation.step.2.fondateur.password.strong', domain:'installation'),
            'fondateur_password_help' => $this->translator->trans('installation.step.2.fondateur.password.help', domain:'installation'),
            'fondateur_btn_create' => $this->translator->trans('installation.step.2.fondateur.btn.create', domain:'installation'),
            'fondateur_loading_msg' => $this->translator->trans('installation.step.2.fondateur.loading.msg', domain:'installation'),
            'fondateur_success' => $this->translator->trans('installation.step.2.fondateur.success', domain:'installation'),
            'debug_titre' => $this->translator->trans('installation.step.2.debug.titre', domain: 'installation'),
            'debug_texte_1' => $this->translator->trans('installation.step.2.debug.texte.1', domain: 'installation'),
            'debug_texte_2' => $this->translator->trans('installation.step.2.debug.texte.2', domain: 'installation'),
            'debug_texte_3' => $this->translator->trans('installation.step.2.debug.texte.3', domain: 'installation'),
            'debug_texte_4' => $this->translator->trans('installation.step.2.debug.texte.4', domain: 'installation'),
            'debug_texte_5' => $this->translator->trans('installation.step.2.debug.texte.5', domain: 'installation'),
            'debug_texte_6' => $this->translator->trans('installation.step.2.debug.texte.6', domain: 'installation'),
        ];
    }
}
