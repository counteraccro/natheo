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
            'paginate' => $this->getTranslatePaginate(),
            'ongletPage' => $this->getTranslateOngletPage()
        ];
    }

    private function getTranslateOngletPage(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.onglet.page.onglet', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.onglet.page.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.onglet.page.description', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.onglet.page.no.result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.onglet.page.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.onglet.page.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.onglet.page.author', domain: 'global_search'),
            'noResultContent' => $this->translator->trans('global_search.onglet.page.no.result.content', domain: 'global_search'),
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
