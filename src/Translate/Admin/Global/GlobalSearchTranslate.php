<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour la recherche globale
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Translate\Admin\Global;

use App\Translate\AppTranslate;

class GlobalSearchTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour la recherche globale
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'subTitlePage' => $this->translator->trans('global_search.index.sub_title', domain: 'global_search'),
            'totalResult' => $this->translator->trans('global_search.index.total_results', domain: 'global_search'),
            'totalNoResult' => $this->translator->trans('global_search.index.total_no_results', domain: 'global_search'),
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
            'onglet' => $this->translator->trans('global_search.user.tab', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.user.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.user.description', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.user.no_result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.user.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.user.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.user.author', domain: 'global_search'),
            'loading' => $this->translator->trans('global_search.user.loading', domain: 'global_search'),
        ];
    }

    /**
     * Traduction de l'onglet tag
     * @return array
     */
    private function getTranslateOngletTag(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.tag.tab', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.tag.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.tag.description', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.tag.no_result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.tag.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.tag.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.tag.author', domain: 'global_search'),
            'loading' => $this->translator->trans('global_search.tag.loading', domain: 'global_search'),
        ];
    }

    /**
     * Traduction de l'onglet page
     * @return array
     */
    private function getTranslateOngletPage(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.page.tab', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.page.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.page.description', domain: 'global_search'),
            'content' => $this->translator->trans('global_search.page.content', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.page.no_result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.page.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.page.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.page.author', domain: 'global_search'),
            'noResultContent' => $this->translator->trans(
                'global_search.page.no_result_content',
                domain: 'global_search',
            ),
            'loading' => $this->translator->trans('global_search.page.loading', domain: 'global_search'),
        ];
    }

    /**
     * Traduction de l'onglet menu
     * @return array
     */
    private function getTranslateOngletMenu(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.menu.tab', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.menu.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.menu.description', domain: 'global_search'),
            'content' => $this->translator->trans('global_search.menu.content', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.menu.no_result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.menu.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.menu.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.menu.author', domain: 'global_search'),
            'noResultContent' => $this->translator->trans(
                'global_search.menu.no_result_content',
                domain: 'global_search',
            ),
            'loading' => $this->translator->trans('global_search.menu.loading', domain: 'global_search'),
        ];
    }

    /**
     * Traduction de l'onglet faq
     * @return array
     */
    private function getTranslateOngletFaq(): array
    {
        return [
            'onglet' => $this->translator->trans('global_search.faq.tab', domain: 'global_search'),
            'title' => $this->translator->trans('global_search.faq.title', domain: 'global_search'),
            'description' => $this->translator->trans('global_search.faq.description', domain: 'global_search'),
            'content' => $this->translator->trans('global_search.faq.content', domain: 'global_search'),
            'noResult' => $this->translator->trans('global_search.faq.no_result', domain: 'global_search'),
            'create' => $this->translator->trans('global_search.faq.create', domain: 'global_search'),
            'update' => $this->translator->trans('global_search.faq.update', domain: 'global_search'),
            'author' => $this->translator->trans('global_search.faq.author', domain: 'global_search'),
            'noResultContent' => $this->translator->trans(
                'global_search.faq.no_result_content',
                domain: 'global_search',
            ),
            'loading' => $this->translator->trans('global_search.faq.loading', domain: 'global_search'),
        ];
    }

    /**
     * Retourne les traductions pour le grid paginate
     * @return array
     */
    private function getTranslatePaginate(): array
    {
        return [
            'page' => $this->translator->trans('global_search.pagination.page', domain: 'global_search'),
            'on' => $this->translator->trans('global_search.pagination.on', domain: 'global_search'),
            'row' => $this->translator->trans('global_search.pagination.row', domain: 'global_search'),
        ];
    }
}
