<?php
/**
 * Class pour la génération des traductions pour le dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Translate\Dashboard;

use App\Utils\Translate\AppTranslate;

class DashboardTranslate extends AppTranslate
{
    public function getTranslate(): array
    {
        return [
            'dashboard_flux_activity' => $this->translator->trans('dashboard.flux.activity', domain: 'dashboard'),
            'dashboard_last_comment' => $this->translator->trans('dashboard.last.comment', domain: 'dashboard'),
        ];
    }
}
