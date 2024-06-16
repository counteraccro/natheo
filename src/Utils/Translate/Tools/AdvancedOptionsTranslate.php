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
            'switch_env_title' => $this->translator->trans('advanced_options.switch.env.title', domain: 'advanced_options'),
            'switch_env_subtitle_prod' => $this->translator->trans('advanced_options.switch.subtitle.env.prod', domain: 'advanced_options'),
            'switch_env_subtitle_dev' => $this->translator->trans('advanced_options.switch.subtitle.env.dev', domain: 'advanced_options'),
            'switch_env_define_dev' => $this->translator->trans('advanced_options.switch.define.env.dev', domain: 'advanced_options'),
            'switch_env_define_prod' => $this->translator->trans('advanced_options.switch.define.env.prod', domain: 'advanced_options'),
        ];
    }
}
