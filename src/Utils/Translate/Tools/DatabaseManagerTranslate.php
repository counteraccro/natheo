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
            'modale_dump_option' => $this->getModaleDumpOptionTranslate()
        ];
    }

    private function getModaleDumpOptionTranslate(): array
    {
        return [
            'title' => $this->translator->trans('database_manager.modale.dump.title', domain: 'database_manager'),
            'sub_title_1' =>  $this->translator->trans('database_manager.modale.dump.sub_title_1',
                domain: 'database_manager'),
            'sub_title_2' =>  $this->translator->trans('database_manager.modale.dump.sub_title_2',
                domain: 'database_manager'),
            'select_all' =>  $this->translator->trans('database_manager.modale.dump.select_all',
                domain: 'database_manager'),
            'select_tables' =>  $this->translator->trans('database_manager.modale.dump.select_tables',
                domain: 'database_manager'),
            'option_data' =>  $this->translator->trans('database_manager.modale.dump.option_data',
                domain: 'database_manager'),
            'option_table' =>  $this->translator->trans('database_manager.modale.dump.option_table',
                domain: 'database_manager'),
            'option_table_data' =>  $this->translator->trans('database_manager.modale.dump.option_table_data',
                domain: 'database_manager'),
            'help_title' =>  $this->translator->trans('database_manager.modale.dump.help.title',
                domain: 'database_manager'),
            'help_body' =>  $this->translator->trans('database_manager.modale.dump.help.body',
                domain: 'database_manager'),

        ];
    }
}
