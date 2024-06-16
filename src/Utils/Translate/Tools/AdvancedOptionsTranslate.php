<?php
/**
 * Class pour la génération des traductions pour les scripts vue de AvancedOptions
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate\Tools;

use App\Utils\Translate\AppTranslate;

class AdvancedOptionsTranslate extends AppTranslate
{
    /**
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'toast_title_success' => $this->translator->trans('advanced_options.toast.title.success', domain: 'advanced_options'),
            'toast_title_error' => $this->translator->trans('advanced_options.toast.title.error', domain: 'advanced_options'),
            'toast_time' => $this->translator->trans('advanced_options.toast.time', domain: 'advanced_options'),
            'switch_env_title' => $this->translator->trans('advanced_options.switch.env.title', domain: 'advanced_options'),
            'switch_env_subtitle_prod' => $this->translator->trans('advanced_options.switch.subtitle.env.prod', domain: 'advanced_options'),
            'switch_env_subtitle_dev' => $this->translator->trans('advanced_options.switch.subtitle.env.dev', domain: 'advanced_options'),
            'switch_env_define_dev' => $this->translator->trans('advanced_options.switch.define.env.dev', domain: 'advanced_options'),
            'switch_env_define_dev_1' => $this->translator->trans('advanced_options.switch.define.env.dev.1', domain: 'advanced_options'),
            'switch_env_define_dev_2' => $this->translator->trans('advanced_options.switch.define.env.dev.2', domain: 'advanced_options'),
            'switch_env_define_dev_3' => $this->translator->trans('advanced_options.switch.define.env.dev.3', domain: 'advanced_options'),
            'switch_env_define_dev_4' => $this->translator->trans('advanced_options.switch.define.env.dev.4', domain: 'advanced_options'),
            'switch_env_define_dev_warning' => $this->translator->trans('advanced_options.switch.define.env.dev.warning', domain: 'advanced_options'),
            'switch_env_define_prod' => $this->translator->trans('advanced_options.switch.define.env.prod', domain: 'advanced_options'),
            'switch_env_define_prod_1' => $this->translator->trans('advanced_options.switch.define.env.prod.1', domain: 'advanced_options'),
            'switch_env_define_prod_2' => $this->translator->trans('advanced_options.switch.define.env.prod.2', domain: 'advanced_options'),
            'switch_env_define_prod_3' => $this->translator->trans('advanced_options.switch.define.env.prod.3', domain: 'advanced_options'),
            'switch_env_define_prod_warning' => $this->translator->trans('advanced_options.switch.define.env.prod.warning', domain: 'advanced_options'),
            'switch_env_btn_dev' => $this->translator->trans('advanced_options.switch.env.btn.dev', domain: 'advanced_options'),
            'switch_env_btn_prod' => $this->translator->trans('advanced_options.switch.env.btn.prod', domain: 'advanced_options'),
        ];
    }
}
