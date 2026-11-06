<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Translate\Admin\Global;

use App\Translate\AppTranslate;

class NotificationTranslate extends AppTranslate
{
    public function getTranslate(): array
    {
        return [
            'loading' => $this->translator->trans('notification.list.loading', domain: 'notification'),
            'empty' => $this->translator->trans('notification.list.empty', domain: 'notification'),
            'onlyNotRead' => $this->translator->trans('notification.filter.unread', domain: 'notification'),
            'readAll' => $this->translator->trans('notification.list.read_all', domain: 'notification'),
            'read' => $this->translator->trans('notification.list.mark_read', domain: 'notification'),
            'noRead' => $this->translator->trans('notification.list.mark_unread', domain: 'notification'),
            'deleteSelected' => $this->translator->trans('notification.list.delete_selected', domain: 'notification'),
            'deleteAll' => $this->translator->trans('notification.list.delete_all', domain: 'notification'),
            'all' => $this->translator->trans('notification.filter.all', domain: 'notification'),
            'statNonLu' => $this->translator->trans('notification.stats.unread', domain: 'notification'),
            'statToday' => $this->translator->trans('notification.stats.today', domain: 'notification'),
            'statAll' => $this->translator->trans('notification.stats.all', domain: 'notification'),
            'confirmTitle' => $this->translator->trans('notification.confirm.title', domain: 'notification'),
            'confirmBtnOK' => $this->translator->trans('notification.confirm.button_ok', domain: 'notification'),
            'confirmBtnNo' => $this->translator->trans('notification.confirm.button_no', domain: 'notification'),
            'confirmMsg' => $this->translator->trans('notification.confirm.message', domain: 'notification'),
            'confirmSingleDelete' => $this->translator->trans(
                'notification.confirm.message_single',
                domain: 'notification',
            ),
            'notification' => $this->getNotificationTranslate(),
        ];
    }

    private function getNotificationTranslate(): array
    {
        return [
            'seeComment' => $this->translator->trans('notification.item.see_comment', domain: 'notification'),
            'read' => $this->translator->trans('notification.item.mark_read', domain: 'notification'),
            'time' => $this->translator->trans('notification.item.time', domain: 'notification'),
        ];
    }
}
