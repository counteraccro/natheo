<?php
/**
 * Class pour la génération des traductions pour les scripts vue pour Mail
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Translate\System;

use App\Utils\Translate\AppTranslate;

class MailTranslate extends AppTranslate
{
    /**
     * Retourne les traductions pour les emails
     * @return array
     */
    public function getTranslate(): array
    {
        return [
            'listLanguage' => $this->translator->trans('mail.list.language', domain: 'mail'),
            'mailContentTitle' => $this->translator->trans('mail.content.title', domain: 'mail'),
            'mailContentSubtitle' => $this->translator->trans('mail.content.subtitle', domain: 'mail'),
            'loading' => $this->translator->trans('mail.loading', domain: 'mail'),
            'titleTrans' => $this->translator->trans('mail.input.trans.title', domain: 'mail'),
            'msgEmptyTitle' => $this->translator->trans('mail.input.trans.title.empty', domain: 'mail'),
            'toast_title_success' => $this->translator->trans('mail.toast.title.success', domain: 'mail'),
            'toast_title_error' => $this->translator->trans('mail.toast.title.error', domain: 'mail'),
            'toast_time' => $this->translator->trans('mail.toast.time', domain: 'mail'),
            'link_save' => $this->translator->trans('mail.link.save', domain: 'mail'),
            'link_send' => $this->translator->trans('mail.link.send', domain: 'mail'),
            'msg_cant_save' => $this->translator->trans('mail.link.cant.save', domain: 'mail'),
        ];
    }
}
