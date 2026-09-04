<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour le dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate\Dashboard;

use App\Utils\Translate\AppTranslate;

class DashboardTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour le dashboard
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'dashboard_flux_activity' => $this->translator->trans('dashboard.flux.activity', domain: 'dashboard'),
            'dashboard_last_comment' => $this->translator->trans('dashboard.last.comment', domain: 'dashboard'),
            'dashboard_last_page' => $this->translator->trans('dashboard.last.page', domain: 'dashboard'),
            'dashboard_help_first_connexion' => $this->getBlockHelpFirstConnexionTranslate(),
            'dashboard_last_comments' => $this->getBlockLastCommentsTranslate(),
            'dashboard_last_pages' => $this->getBlockLastPagesTranslate(),
        ];
    }

    /**
     * Retourne les traductions du block première connexion
     * @return array
     */
    public function getBlockHelpFirstConnexionTranslate(): array
    {
        return [
            'title' => $this->translator->trans('dashboard.block.help.first.connexion.title', domain: 'dashboard'),
            'loading' => $this->translator->trans('dashboard.block.help.first.connexion.loading', domain: 'dashboard'),
            'sub_title' => $this->translator->trans(
                'dashboard.block.help.first.connexion.sub_title',
                domain: 'dashboard',
            ),
            'text_1' => $this->translator->trans('dashboard.block.help.first.connexion.text_1', domain: 'dashboard'),
            'text_end_success' => $this->translator->trans(
                'dashboard.block.help.first.connexion.text.end.success',
                domain: 'dashboard',
            ),
            'text_end' => $this->translator->trans(
                'dashboard.block.help.first.connexion.text.end',
                domain: 'dashboard',
            ),
            'btn_def_hide' => $this->translator->trans(
                'dashboard.block.help.first.connexion.btn.hide',
                domain: 'dashboard',
            ),
            'modal_confirm_title' => $this->translator->trans(
                'dashboard.block.help.first.connexion.modal.confirm.title',
                domain: 'dashboard',
            ),
            'modal_confirm_body_1' => $this->translator->trans(
                'dashboard.block.help.first.connexion.modal.confirm.body.1',
                domain: 'dashboard',
            ),
            'modal_confirm_body_2' => $this->translator->trans(
                'dashboard.block.help.first.connexion.modal.confirm.body.2',
                domain: 'dashboard',
            ),
            'modal_confirm_btn_ok' => $this->translator->trans(
                'dashboard.block.help.first.connexion.modal.confirm.btn_ok',
                domain: 'dashboard',
            ),
            'modal_confirm_btn_ko' => $this->translator->trans(
                'dashboard.block.help.first.connexion.modal.confirm.btn_ko',
                domain: 'dashboard',
            ),
            'msg_hide_success' => $this->translator->trans(
                'dashboard.block.help.first.connexion.msg.hide.success',
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
            'title' => $this->translator->trans('dashboard.block.last.comments.title', domain: 'dashboard'),
            'loading' => $this->translator->trans('dashboard.block.last.comments.loading', domain: 'dashboard'),
            'table_id' => $this->translator->trans('dashboard.block.last.comments.table.id', domain: 'dashboard'),
            'table_author' => $this->translator->trans(
                'dashboard.block.last.comments.table.author',
                domain: 'dashboard',
            ),
            'table_status' => $this->translator->trans(
                'dashboard.block.last.comments.table.status',
                domain: 'dashboard',
            ),
            'table_date' => $this->translator->trans('dashboard.block.last.comments.table.date', domain: 'dashboard'),
            'link_comment' => $this->translator->trans(
                'dashboard.block.last.comments.link.comment',
                domain: 'dashboard',
            ),
        ];
    }

    /**
     * Retourne les traductions du block derniers commentaires
     * @return array
     */
    public function getBlockLastPagesTranslate(): array
    {
        return [
            'title' => $this->translator->trans('dashboard.block.last.pages.title', domain: 'dashboard'),
            'loading' => $this->translator->trans('dashboard.block.last.pages.loading', domain: 'dashboard'),
            'link_page' => $this->translator->trans('dashboard.block.last.pages.link.page', domain: 'dashboard'),
            'table_id' => $this->translator->trans('dashboard.block.last.pages.table.id', domain: 'dashboard'),
            'table_title' => $this->translator->trans('dashboard.block.last.pages.table.title', domain: 'dashboard'),
            'table_status' => $this->translator->trans('dashboard.block.last.pages.table.status', domain: 'dashboard'),
            'table_date' => $this->translator->trans('dashboard.block.last.pages.table.date', domain: 'dashboard'),
        ];
    }

    public function getDashboardStatisticsTranslate(): array
    {
        return [
            'total_article' => $this->translator->trans('dashboard.statistics.total.article', domain: 'dashboard'),
            'comments' => $this->translator->trans('dashboard.statistics.total.comments', domain: 'dashboard'),
            'comments_waiting' => $this->translator->trans(
                'dashboard.statistics.total.comments.waiting',
                domain: 'dashboard',
            ),
            'users' => $this->translator->trans('dashboard.statistics.total.users', domain: 'dashboard'),
            'views' => $this->translator->trans('dashboard.statistics.total.views', domain: 'dashboard'),
            'link_pages' => $this->translator->trans('dashboard.statistics.link.pages', domain: 'dashboard'),
            'link_users' => $this->translator->trans('dashboard.statistics.link.users', domain: 'dashboard'),
            'info_cache' => $this->translator->trans('dashboard.statistics.info.cache', domain: 'dashboard'),
        ];
    }
}
