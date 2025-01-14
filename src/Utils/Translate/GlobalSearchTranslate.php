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
            'totalResult' => $this->translator->trans('global_search.total.results', domain: 'global_search'),
            'totalNoResult' => $this->translator->trans('global_search.total.no.results', domain: 'global_search'),
            'paginate' => $this->getTranslatePaginate(),
            'ongletPage' => $this->getTranslateOngletPage(),
            'ongletMenu' => $this->getTranslateOngletMenu(),
            'ongletFaq' => $this->getTranslateOngletFaq(),
            'ongletTag' => $this->getTranslateOngletTag(),
            'ongletUser' => $this->getTranslateOngletUser(),
        ];
    }

    /**
     * Traduction de l'onglet user
     * @return array
     */
    private function getTranslateOngletUser(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.onglet.user.onglet', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.onglet.user.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.onglet.user.description', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.onglet.user.no.result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.onglet.user.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.onglet.user.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.onglet.user.author', domain: 'global_search'),
            'loading' => $this->translator->trans('global_search.onglet.user.loading', domain: 'global_search'),
        ];
    }

    /**
     * Traduction de l'onglet tag
     * @return array
     */
    private function getTranslateOngletTag(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.onglet.tag.onglet', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.onglet.tag.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.onglet.tag.description', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.onglet.tag.no.result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.onglet.tag.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.onglet.tag.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.onglet.tag.author', domain: 'global_search'),
            'loading' => $this->translator->trans('global_search.onglet.tag.loading', domain: 'global_search'),
        ];
    }

    /**
     * Traduction de l'onglet page
     * @return array
     */
    private function getTranslateOngletPage(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.onglet.page.onglet', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.onglet.page.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.onglet.page.description', domain: 'global_search'),
            'content' => $this->translator->trans('global_search.onglet.page.content', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.onglet.page.no.result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.onglet.page.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.onglet.page.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.onglet.page.author', domain: 'global_search'),
            'noResultContent' => $this->translator->trans('global_search.onglet.page.no.result.content', domain: 'global_search'),
            'loading' => $this->translator->trans('global_search.onglet.page.loading', domain: 'global_search'),
        ];
    }

    /**
     * Traduction de l'onglet menu
     * @return array
     */
    private function getTranslateOngletMenu(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.onglet.menu.onglet', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.onglet.menu.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.onglet.menu.description', domain: 'global_search'),
            'content' => $this->translator->trans('global_search.onglet.menu.content', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.onglet.menu.no.result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.onglet.menu.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.onglet.menu.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.onglet.menu.author', domain: 'global_search'),
            'noResultContent' => $this->translator->trans('global_search.onglet.menu.no.result.content', domain: 'global_search'),
            'loading' => $this->translator->trans('global_search.onglet.menu.loading', domain: 'global_search'),
        ];
    }

    /**
     * Traduction de l'onglet faq
     * @return array
     */
    private function getTranslateOngletFaq(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.onglet.faq.onglet', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.onglet.faq.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.onglet.faq.description', domain: 'global_search'),
            'content' => $this->translator->trans('global_search.onglet.faq.content', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.onglet.faq.no.result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.onglet.faq.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.onglet.faq.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.onglet.faq.author', domain: 'global_search'),
            'noResultContent' => $this->translator->trans('global_search.onglet.faq.no.result.content', domain: 'global_search'),
            'loading' => $this->translator->trans('global_search.onglet.faq.loading', domain: 'global_search'),
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
