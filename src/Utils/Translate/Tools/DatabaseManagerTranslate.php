<?php
/**
 * Class pour la génération des traductions pour les scripts vue de DatabaseManager
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate\Tools;

use App\Utils\Translate\AppTranslate;

class DatabaseManagerTranslate extends AppTranslate
{
    public function getTranslate(): array
    {
        return [
            'loading' => $this->translator->trans('database_manager.loading', domain: 'database_manager'),
            'nb_row_total' => $this->translator->trans('database_manager.nb.row.total', domain: 'database_manager'),
        ];
    }
}
