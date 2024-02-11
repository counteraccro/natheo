<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour la génération du grid
 */

namespace App\Service\Admin;

class GridService extends AppAdminService
{
    /**
     * Cle pour les actions dans les tableaux grid
     */
    const KEY_ACTION = 'action';

    /**
     * Clé pour le nombre d'éléments
     */
    const KEY_NB = 'nb';

    /**
     * Clé pour les données
     */
    const KEY_DATA = 'data';

    /**
     * Clé pour les colonnes
     */
    const KEY_COLUMN = 'column';

    /**
     * Retourne l'ensemble des données obligatoires pour le grid
     * @param array $tab
     * @return array
     */
    public function addAllDataRequiredGrid(array $tab): array
    {
        $tab = $this->addOptionsSelectLimit($tab);
        return $this->addTranslateGrid($tab);
    }

    /**
     * Ajoute le choix des limits dans le tableau de donnée du GRID
     * @param array $tab
     * @return array
     */
    public function addOptionsSelectLimit(array $tab): array
    {
        $optionLimitGrid = [5 => 5, 10 => 10, 20 => 20, 50 => 50, 100 => 100];
        $tab['listLimit'] = json_encode($optionLimitGrid);
        return $tab;
    }

    /**
     * Ajoute les traductions au tableau de donnée du GRID
     * @param array $tab
     * @return array
     */
    public function addTranslateGrid(array $tab): array
    {

        $tab['translate'] = [
            'genericGrid' => json_encode([
                'placeholder' => $this->translator->trans('grid.search.placeholder', domain: 'grid'),
                'loading' => $this->translator->trans('grid.loading', domain: 'grid'),
                'titleSuccess' => $this->translator->trans('grid.success.titre', domain: 'grid'),
                'titleError' => $this->translator->trans('grid.error.titre', domain: 'grid'),
                'time' => $this->translator->trans('grid.toast.time', domain: 'grid'),
                'confirmTitle' => $this->translator->trans('grid.confirm.titre', domain: 'grid'),
                'confirmText' => $this->translator->trans('grid.confirm.texte', domain: 'grid'),
                'confirmBtnOK' => $this->translator->trans('grid.confirm.btn.ok', domain: 'grid'),
                'confirmBtnNo' => $this->translator->trans('grid.confirm.btn.no', domain: 'grid'),

            ]),
            'gridPaginate' => json_encode([
                'page' => $this->translator->trans('grid.page', [], domain: 'grid'),
                'on' => $this->translator->trans('grid.on', [], domain: 'grid'),
                'row' => $this->translator->trans('grid.row', [], domain: 'grid')
            ]),
            'grid' => json_encode([
                'noresult' => $this->translator->trans('grid.no.result', [], domain: 'grid')
            ])
        ];

        return $tab;
    }

    /**
     * Format le role de symfony en donnée à afficher pour le grid
     * @param string $role
     * @return string
     */
    public function renderRole(string $role): string
    {
        $tabRole = [
            'ROLE_USER' => $this->translator->trans('global.role.user', domain: 'global'),
            'ROLE_CONTRIBUTEUR' => $this->translator->trans('global.role.contributeur', domain: 'global'),
            'ROLE_ADMIN' => $this->translator->trans('global.role.admin', domain: 'global'),
            'ROLE_SUPER_ADMIN' => $this->translator->trans('global.role.superadmin', domain: 'global')
        ];
        return $tabRole[$role];
    }
}
