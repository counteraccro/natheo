<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue de grid
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Translate\Admin\Global;

use App\Translate\AppTranslate;

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
            'grid' => $this->getTranslateGrid(),
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
            'placeholderBddSearch' => $this->translator->trans('grid.search.placeholder_database', domain: 'grid'),
            'placeholderTableSearch' => $this->translator->trans(
                'grid.search.placeholder_table',
                domain: 'grid',
            ),
            'textBddSearch' => $this->translator->trans('grid.search.text_database', domain: 'grid'),
            'titleSearch' => $this->translator->trans('grid.search.title', domain: 'grid'),
            'textTableSearch' => $this->translator->trans('grid.search.text_table', domain: 'grid'),
            'textShowQuery' => $this->translator->trans('grid.query.show', domain: 'grid'),
            'textHideQuery' => $this->translator->trans('grid.query.hide', domain: 'grid'),
            'textShowTrieOption' => $this->translator->trans('grid.sort.show', domain: 'grid'),
            'textHideTrieOption' => $this->translator->trans('grid.sort.hide', domain: 'grid'),
            'btnSearch' => $this->translator->trans('grid.search.button', domain: 'grid'),
            'loading' => $this->translator->trans('grid.list.loading', domain: 'grid'),
            'titleSuccess' => $this->translator->trans('grid.toast.success_title', domain: 'grid'),
            'titleError' => $this->translator->trans('grid.toast.error_title', domain: 'grid'),
            'time' => $this->translator->trans('grid.toast.time', domain: 'grid'),
            'confirmTitle' => $this->translator->trans('grid.confirm.title', domain: 'grid'),
            'confirmText' => $this->translator->trans('grid.confirm.text', domain: 'grid'),
            'confirmBtnOK' => $this->translator->trans('grid.confirm.button_ok', domain: 'grid'),
            'confirmBtnNo' => $this->translator->trans('grid.confirm.button_no', domain: 'grid'),
            'copySuccess' => $this->translator->trans('grid.query.copy_success', domain: 'grid'),
            'copyError' => $this->translator->trans('grid.query.copy_error', domain: 'grid'),
            'queryTitle' => $this->translator->trans('grid.query.title', domain: 'grid'),
            'filterOnlyMe' => $this->translator->trans('grid.filter.only_me', domain: 'grid'),
            'filterAll' => $this->translator->trans('grid.filter.all', domain: 'grid'),
            'titleTrieOption' => $this->translator->trans('grid.sort.title', domain: 'grid'),
            'titleTrieOptionSubMenu' => $this->translator->trans('grid.sort.sub_title', domain: 'grid'),
            'trieOptionListeField' => $this->translator->trans('grid.sort.field', domain: 'grid'),
            'trieOptionListeOrder' => $this->translator->trans('grid.sort.order', domain: 'grid'),
            'trieOptionBtn' => $this->translator->trans('grid.sort.button', domain: 'grid'),
        ];
    }

    /**
     * Retourne les traductions pour le grid paginate
     * @return array
     */
    private function getTranslateGridPaginate(): array
    {
        return [
            'page' => $this->translator->trans('grid.pagination.page', [], domain: 'grid'),
            'on' => $this->translator->trans('grid.pagination.on', [], domain: 'grid'),
            'row' => $this->translator->trans('grid.pagination.row', [], domain: 'grid'),
        ];
    }

    /**
     * Retourne les traductions pour le grid
     * @return array
     */
    private function getTranslateGrid(): array
    {
        return [
            'noresult' => $this->translator->trans('grid.list.no_result', [], domain: 'grid'),
        ];
    }
}
