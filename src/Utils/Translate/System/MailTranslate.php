<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue pour Mail
 * @author Gourdon Aymeric
 * @version 1.1
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
            'titleTrans' => $this->translator->trans('mail.input.trans.title', domain: 'mail'),
            'msgEmptyTitle' => $this->translator->trans('mail.input.trans.title.empty', domain: 'mail'),
            'link_save' => $this->translator->trans('mail.link.save', domain: 'mail'),
            'link_send' => $this->translator->trans('mail.link.send', domain: 'mail'),
            'msg_cant_save' => $this->translator->trans('mail.link.cant.save', domain: 'mail'),
            'msg_error' => $this->translator->trans('mail.error.load', domain: 'mail'),
        ];
    }
}
