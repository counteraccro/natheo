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
                'loading' => $this->translator->trans('grid.loading')
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
}