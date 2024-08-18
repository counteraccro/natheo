<?php

namespace App\Utils\Translate\Installation;

use App\Utils\Translate\AppTranslate;

class InstallationTranslate extends AppTranslate
{
    public function getTranslateStepOne(): array
    {
        return [
            'toast' => $this->getTranslateToast(),
            'title' => $this->translator->trans('installation.step.1.titre', domain:'installation'),
            'title_h1' => $this->translator->trans('installation.step.1.titre.h1', domain:'installation'),
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
