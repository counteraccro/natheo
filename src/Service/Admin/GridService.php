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
                'placeholder' => $this->translator->trans('grid.search.placeholder'),
                'loading' => $this->translator->trans('grid.loading'),
                'titleSuccess' => $this->translator->trans('grid.success.titre'),
                'confirmTitle' => $this->translator->trans('grid.confirm.titre'),
                'confirmText' => $this->translator->trans('grid.confirm.texte'),
                'confirmBtnOK' => $this->translator->trans('grid.confirm.btn.ok'),
                'confirmBtnNo' => $this->translator->trans('grid.confirm.btn.no'),
            ]),
            'gridPaginate' => json_encode([
                'page' => $this->translator->trans('grid.page'),
                'on' => $this->translator->trans('grid.on'),
                'row' => $this->translator->trans('grid.row')
            ]),
            'grid' => json_encode([
                'noresult' => $this->translator->trans('grid.no.result')
            ])
        ];

        return $tab;
    }

    /**
     * Format le role de symfony en donnée à afficher pour le grid
     * @param string $role
     * @return string
     */
    public function renderRole(string $role ): string
    {
        $tabRole = [
            'ROLE_USER' => $this->translator->trans('global.role.user'),
            'ROLE_CONTRIBUTEUR' => $this->translator->trans('global.role.contributeur'),
            'ROLE_ADMIN' => $this->translator->trans('global.role.admin'),
            'ROLE_SUPER_ADMIN' => $this->translator->trans('global.role.superadmin')
        ];

        return $tabRole[$role];
    }
}