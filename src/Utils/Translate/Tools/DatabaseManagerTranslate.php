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
    /**
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'loading' => $this->translator->trans('database_manager.loading', domain: 'database_manager'),
            'btn_generate_dump' => $this->translator->trans(
                'database_manager.btn_generate_dump',
                domain: 'database_manager',
            ),
            'btn_schema_bdd' => $this->translator->trans('database_manager.btn_schema_bdd', domain: 'database_manager'),
            'btn_schema_table' => $this->translator->trans(
                'database_manager.btn_schema_table',
                domain: 'database_manager',
            ),
            'btn_liste_dump' => $this->translator->trans('database_manager.btn_liste_dump', domain: 'database_manager'),
            'stat_nb_table' => $this->translator->trans('database_manager.stat.nb.table', domain: 'database_manager'),
            'stat_nb_row' => $this->translator->trans('database_manager.stat.nb.row', domain: 'database_manager'),
            'stat_size' => $this->translator->trans('database_manager.stat.size', domain: 'database_manager'),
            'action' => $this->translator->trans('database_manager.action', domain: 'database_manager'),
            'dump_option' => $this->getTranslateDumpOption(),
            'list_dump' => $this->getTranslateListDump(),
        ];
    }

    /**
     * Retourne les traductions de la modal pour générer les dumps
     * @return array
     */
    private function getTranslateDumpOption(): array
    {
        return [
            'title' => $this->translator->trans('database_manager.dump.title', domain: 'database_manager'),
            'sub_title_1' => $this->translator->trans('database_manager.dump.sub_title_1', domain: 'database_manager'),
            'sub_title_2' => $this->translator->trans('database_manager.dump.sub_title_2', domain: 'database_manager'),
            'select_all' => $this->translator->trans('database_manager.dump.select_all', domain: 'database_manager'),
            'filename_label' => $this->translator->trans(
                'database_manager.dump.filename.label',
                domain: 'database_manager',
            ),
            'filename_help' => $this->translator->trans(
                'database_manager.dump.filename.help',
                domain: 'database_manager',
            ),
            'select_tables' => $this->translator->trans(
                'database_manager.dump.select_tables',
                domain: 'database_manager',
            ),
            'option_data_label' => $this->translator->trans(
                'database_manager.dump.option_data.label',
                domain: 'database_manager',
            ),
            'option_data' => $this->translator->trans('database_manager.dump.option_data', domain: 'database_manager'),
            'option_table' => $this->translator->trans(
                'database_manager.dump.option_table',
                domain: 'database_manager',
            ),
            'option_table_data' => $this->translator->trans(
                'database_manager.dump.option_table_data',
                domain: 'database_manager',
            ),
            'help_title' => $this->translator->trans('database_manager.dump.help.title', domain: 'database_manager'),
            'help_body' => $this->translator->trans('database_manager.dump.help.body', domain: 'database_manager'),
            'warning_title' => $this->translator->trans(
                'database_manager.dump.warning.title',
                domain: 'database_manager',
            ),
            'warning_body' => $this->translator->trans(
                'database_manager.dump.warning.body',
                domain: 'database_manager',
            ),
            'btn_generate' => $this->translator->trans(
                'database_manager.dump.btn.generate',
                domain: 'database_manager',
            ),
        ];
    }

    /**
     * Retourne les traductions du tableau du schema de la table
     * @return array
     */
    private function getTranslateListDump(): array
    {
        return [
            'title' => $this->translator->trans('database_manager.list.dump.title', domain: 'database_manager'),
            'no_data' => $this->translator->trans('database_manager.list.dump.no.data', domain: 'database_manager'),
            'btn_refresh' => $this->translator->trans(
                'database_manager.list.dump.btn.refresh',
                domain: 'database_manager',
            ),
            'btn_download' => $this->translator->trans(
                'database_manager.list.dump.btn.download',
                domain: 'database_manager',
            ),
            'title_modale_confirm' => $this->translator->trans(
                'database_manager.list.dump.confirm.delete.title',
                domain: 'database_manager',
            ),
            'text_modale_confirm' => $this->translator->trans(
                'database_manager.list.dump.confirm.delete.texte',
                domain: 'database_manager',
            ),
            'btn_go' => $this->translator->trans(
                'database_manager.list.dump.confirm.btn.ok',
                domain: 'database_manager',
            ),
            'btn_undo' => $this->translator->trans(
                'database_manager.list.dump.confirm.btn.ko',
                domain: 'database_manager',
            ),
        ];
    }
}
