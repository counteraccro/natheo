<?php
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
            'dashboard_help_first_connexion' => $this->getBlockHelpFirstConnexionTranslate(),
            'dashboard_last_comments' => $this->getBlockLastCommentsTranslate()
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
            'sub_title' => $this->translator->trans('dashboard.block.help.first.connexion.sub_title', domain: 'dashboard'),
            'text_1' => $this->translator->trans('dashboard.block.help.first.connexion.text_1', domain: 'dashboard'),
            'text_end_success' => $this->translator->trans('dashboard.block.help.first.connexion.text.end.success', domain: 'dashboard'),
            'text_end' => $this->translator->trans('dashboard.block.help.first.connexion.text.end', domain: 'dashboard'),
            'btn_def_hide' => $this->translator->trans('dashboard.block.help.first.connexion.btn.hide', domain: 'dashboard'),
            'modal_confirm_title' => $this->translator->trans('dashboard.block.help.first.connexion.modal.confirm.title', domain: 'dashboard'),
            'modal_confirm_body_1' => $this->translator->trans('dashboard.block.help.first.connexion.modal.confirm.body.1', domain: 'dashboard'),
            'modal_confirm_body_2' => $this->translator->trans('dashboard.block.help.first.connexion.modal.confirm.body.2', domain: 'dashboard'),
            'modal_confirm_btn_ok' => $this->translator->trans('dashboard.block.help.first.connexion.modal.confirm.btn_ok', domain: 'dashboard'),
            'modal_confirm_btn_ko' => $this->translator->trans('dashboard.block.help.first.connexion.modal.confirm.btn_ko', domain: 'dashboard'),
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
        ];
    }
}
