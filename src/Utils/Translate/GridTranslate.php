<?php
/**
 * Class pour la génération des traductions pour les scripts vue de grid
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate;

class GridTranslate extends AppTranslate
{
    /**
     * Retourne les traductions du grid
     * @return array[]
     */
    public function getTranslate(): array
    {
        return [
            'genericGrid' => $this->getTranslateGenericGrid(),
            'gridPaginate' => $this->getTranslateGridPaginate(),
            'grid' => $this->getTranslateGrid()
        ];
    }

    /**
     * Retourne les traductions pour le grid générique
     * @return array
     */
    private function getTranslateGenericGrid(): array
    {
        return [
            'placeholder' => $this->translator->trans('grid.search.placeholder', domain: 'grid'),
            'placeholderBddSearch' => $this->translator->trans('grid.search.placeholder.bdd.search', domain: 'grid'),
            'btnSearch' => $this->translator->trans('grid.btn.search', domain: 'grid'),
            'loading' => $this->translator->trans('grid.loading', domain: 'grid'),
            'titleSuccess' => $this->translator->trans('grid.success.titre', domain: 'grid'),
            'titleError' => $this->translator->trans('grid.error.titre', domain: 'grid'),
            'time' => $this->translator->trans('grid.toast.time', domain: 'grid'),
            'confirmTitle' => $this->translator->trans('grid.confirm.titre', domain: 'grid'),
            'confirmText' => $this->translator->trans('grid.confirm.texte', domain: 'grid'),
            'confirmBtnOK' => $this->translator->trans('grid.confirm.btn.ok', domain: 'grid'),
            'confirmBtnNo' => $this->translator->trans('grid.confirm.btn.no', domain: 'grid'),
            'copySuccess' => $this->translator->trans('grid.copy.success', domain: 'grid'),
            'copyError' => $this->translator->trans('grid.copy.error', domain: 'grid'),
            'queryTitle' => $this->translator->trans('grid.query.title', domain: 'grid'),
        ];
    }

    /**
     * Retourne les traductions pour le grid paginate
     * @return array
     */
    private function getTranslateGridPaginate(): array
    {
        return [
            'page' => $this->translator->trans('grid.page', [], domain: 'grid'),
            'on' => $this->translator->trans('grid.on', [], domain: 'grid'),
            'row' => $this->translator->trans('grid.row', [], domain: 'grid')
        ];
    }

    /**
     * Retourne les traductions pour le grid
     * @return array
     */
    private function getTranslateGrid(): array
    {
        return [
            'noresult' => $this->translator->trans('grid.no.result', [], domain: 'grid')
        ];
    }
}
