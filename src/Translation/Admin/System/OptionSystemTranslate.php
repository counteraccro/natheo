<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour le script vue des options système
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Translation\Admin\System;

use App\Translation\AppTranslate;

class OptionSystemTranslate extends AppTranslate
{
    /**
     * Retourne les traductions de la confirmation des options de type action
     * @return array{title: string, body: string, cancel: string, confirm: string, error: string}
     */
    public function getTranslate(): array
    {
        return [
            'title' => $this->translator->trans('option_system.consent_reset.title', domain: 'option_system'),
            'body' => $this->translator->trans('option_system.consent_reset.body', domain: 'option_system'),
            'cancel' => $this->translator->trans('option_system.consent_reset.cancel', domain: 'option_system'),
            'confirm' => $this->translator->trans('option_system.consent_reset.confirm', domain: 'option_system'),
            'error' => $this->translator->trans('option_system.consent_reset.error', domain: 'option_system'),
        ];
    }
}
