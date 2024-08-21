<?php

namespace App\Utils\Translate\Installation;

use App\Utils\Translate\AppTranslate;

class InstallationTranslate extends AppTranslate
{
    public function getTranslateStepOne(): array
    {
        return [
            'toast' => $this->getTranslateToast(),
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
        ];
    }

    private function getTranslateToast(): array
    {
        return [
            'toast_title_success' => $this->translator->trans('installation.toast.title.success', domain: 'installation'),
            'toast_time' => $this->translator->trans('installation.toast.time', domain: 'installation'),
            'toast_title_error' => $this->translator->trans('installation.toast.title.error', domain: 'installation'),
        ];
    }
}
