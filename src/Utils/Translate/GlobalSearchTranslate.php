<?php

namespace App\Utils\Translate;

class GlobalSearchTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour la recherche globale
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'subTitlePage' => $this->translator->trans('global_search.sub.title.page', domain: 'global_search'),
            'loadingPage' => $this->translator->trans('global_search.page.loading', domain: 'global_search'),
            'paginate' => $this->getTranslatePaginate()
        ];
    }

    /**
     * Retourne les traductions pour le grid paginate
     * @return array
     */
    private function getTranslatePaginate(): array
    {
        return [
            'page' => $this->translator->trans('global_search.paginate.page', domain: 'global_search'),
            'on' => $this->translator->trans('global_search.paginate.on', domain: 'global_search'),
            'row' => $this->translator->trans('global_search.paginate.row', domain: 'global_search')
        ];
    }
}
