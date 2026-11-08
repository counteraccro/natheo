<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour le dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Translate\Admin\Global;

use App\Translate\AppTranslate;

class DashboardTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour le dashboard
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'dashboard_help_first_connexion' => $this->getBlockHelpFirstConnexionTranslate(),
            'dashboard_last_comments' => $this->getBlockLastCommentsTranslate(),
            'dashboard_last_pages' => $this->getBlockLastPagesTranslate(),
            'dashboard_page_most_viewed' => $this->getBlockPagesMostViewedTranslate(),
            'dashboard_pages_pending_validation' => $this->getBlockPagesPendingValidationTranslate(),
        ];
    }

    /**
     * Retourne les traductions du block première connexion
     * @return array
     */
    public function getBlockHelpFirstConnexionTranslate(): array
    {
        return [
            'title' => $this->translator->trans('dashboard.help_first_connection.title', domain: 'dashboard'),
            'loading' => $this->translator->trans('dashboard.help_first_connection.loading', domain: 'dashboard'),
            'sub_title' => $this->translator->trans(
                'dashboard.help_first_connection.sub_title',
                domain: 'dashboard',
            ),
            'text_1' => $this->translator->trans('dashboard.help_first_connection.text_1', domain: 'dashboard'),
            'text_end_success' => $this->translator->trans(
                'dashboard.help_first_connection.text_end_success',
                domain: 'dashboard',
            ),
            'text_end' => $this->translator->trans(
                'dashboard.help_first_connection.text_end',
                domain: 'dashboard',
            ),
            'btn_def_hide' => $this->translator->trans(
                'dashboard.help_first_connection.button_hide',
                domain: 'dashboard',
            ),
            'modal_confirm_title' => $this->translator->trans(
                'dashboard.help_first_connection.modal.title',
                domain: 'dashboard',
            ),
            'modal_confirm_body_1' => $this->translator->trans(
                'dashboard.help_first_connection.modal.body_1',
                domain: 'dashboard',
            ),
            'modal_confirm_body_2' => $this->translator->trans(
                'dashboard.help_first_connection.modal.body_2',
                domain: 'dashboard',
            ),
            'modal_confirm_btn_ok' => $this->translator->trans(
                'dashboard.help_first_connection.modal.button_ok',
                domain: 'dashboard',
            ),
            'modal_confirm_btn_ko' => $this->translator->trans(
                'dashboard.help_first_connection.modal.button_ko',
                domain: 'dashboard',
            ),
            'msg_hide_success' => $this->translator->trans(
                'dashboard.help_first_connection.hide_success',
                domain: 'dashboard',
            ),
        ];
    }

    /**
     * Retourne les traductions du block derniers commentaires
     * @return array
     */
    public function getBlockLastCommentsTranslate(): array
    {
        return [
            'title' => $this->translator->trans('dashboard.last_comments.title', domain: 'dashboard'),
            'loading' => $this->translator->trans('dashboard.last_comments.loading', domain: 'dashboard'),
            'table_id' => $this->translator->trans('dashboard.last_comments.column_id', domain: 'dashboard'),
            'table_author' => $this->translator->trans(
                'dashboard.last_comments.column_author',
                domain: 'dashboard',
            ),
            'table_status' => $this->translator->trans(
                'dashboard.last_comments.column_status',
                domain: 'dashboard',
            ),
            'table_date' => $this->translator->trans('dashboard.last_comments.column_date', domain: 'dashboard'),
            'link_comment' => $this->translator->trans(
                'dashboard.last_comments.link_comment',
                domain: 'dashboard',
            ),
        ];
    }

    /**
     * Retourne les traductions du block dernières pages
     * @return array
     */
    public function getBlockLastPagesTranslate(): array
    {
        return [
            'title' => $this->translator->trans('dashboard.last_pages.title', domain: 'dashboard'),
            'loading' => $this->translator->trans('dashboard.last_pages.loading', domain: 'dashboard'),
            'link_page' => $this->translator->trans('dashboard.last_pages.link_page', domain: 'dashboard'),
            'table_id' => $this->translator->trans('dashboard.last_pages.column_id', domain: 'dashboard'),
            'table_title' => $this->translator->trans('dashboard.last_pages.column_title', domain: 'dashboard'),
            'table_status' => $this->translator->trans('dashboard.last_pages.column_status', domain: 'dashboard'),
            'table_date' => $this->translator->trans('dashboard.last_pages.column_date', domain: 'dashboard'),
        ];
    }

    /**
     * Retourne les traductions du block page plus vue
     * @return array
     */
    public function getBlockPagesMostViewedTranslate(): array
    {
        return [
            'title' => $this->translator->trans('dashboard.most_viewed_pages.title', domain: 'dashboard'),
            'link_page' => $this->translator->trans('dashboard.most_viewed_pages.link_page', domain: 'dashboard'),
            'table_id' => $this->translator->trans('dashboard.most_viewed_pages.column_id', domain: 'dashboard'),
            'table_title' => $this->translator->trans(
                'dashboard.most_viewed_pages.column_title',
                domain: 'dashboard',
            ),
            'table_view' => $this->translator->trans(
                'dashboard.most_viewed_pages.column_views',
                domain: 'dashboard',
            ),
            'table_date' => $this->translator->trans(
                'dashboard.most_viewed_pages.column_date',
                domain: 'dashboard',
            ),
        ];
    }

    /**
     * Retourne les traductions du block pages à valider
     * @return array
     */
    public function getBlockPagesPendingValidationTranslate(): array
    {
        return [
            'title' => $this->translator->trans(
                'dashboard.pages_pending_validation.title',
                domain: 'dashboard',
            ),
            'link_page' => $this->translator->trans(
                'dashboard.pages_pending_validation.link_page',
                domain: 'dashboard',
            ),
            'column_id' => $this->translator->trans(
                'dashboard.pages_pending_validation.column_id',
                domain: 'dashboard',
            ),
            'column_title' => $this->translator->trans(
                'dashboard.pages_pending_validation.column_title',
                domain: 'dashboard',
            ),
            'column_author' => $this->translator->trans(
                'dashboard.pages_pending_validation.column_author',
                domain: 'dashboard',
            ),
            'column_date' => $this->translator->trans(
                'dashboard.pages_pending_validation.column_date',
                domain: 'dashboard',
            ),
            'column_action' => $this->translator->trans(
                'dashboard.pages_pending_validation.column_action',
                domain: 'dashboard',
            ),
            'link_edit' => $this->translator->trans(
                'dashboard.pages_pending_validation.link_edit',
                domain: 'dashboard',
            ),
            'empty' => $this->translator->trans(
                'dashboard.pages_pending_validation.empty',
                domain: 'dashboard',
            ),
        ];
    }

    /**
     * Retourne les traductions du block statistiques
     * @return array
     */
    public function getDashboardStatisticsTranslate(): array
    {
        return [
            'total_article' => $this->translator->trans('dashboard.statistics.total_pages', domain: 'dashboard'),
            'comments' => $this->translator->trans('dashboard.statistics.total_comments', domain: 'dashboard'),
            'comments_waiting' => $this->translator->trans(
                'dashboard.statistics.total_comments_waiting',
                domain: 'dashboard',
            ),
            'users' => $this->translator->trans('dashboard.statistics.total_users', domain: 'dashboard'),
            'views' => $this->translator->trans('dashboard.statistics.total_views', domain: 'dashboard'),
            'link_pages' => $this->translator->trans('dashboard.statistics.link_pages', domain: 'dashboard'),
            'link_users' => $this->translator->trans('dashboard.statistics.link_users', domain: 'dashboard'),
            'info_cache' => $this->translator->trans('dashboard.statistics.info_cache', domain: 'dashboard'),
        ];
    }
}
