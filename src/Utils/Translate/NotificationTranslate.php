<?php
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate;

class NotificationTranslate extends AppTranslate
{
    public function getTranslate(): array
    {
        return [
            'nb_notifification_show_start' => $this->translator->trans(
                'notification.nb.show.start',
                domain: 'notification',
            ),
            'nb_notifification_show_end' => $this->translator->trans(
                'notification.nb.show.end',
                domain: 'notification',
            ),
            'loading' => $this->translator->trans('notification.loading', domain: 'notification'),
            'empty' => $this->translator->trans('notification.empty', domain: 'notification'),
            'onlyNotRead' => $this->translator->trans('notification.only_not_read', domain: 'notification'),
            'readAll' => $this->translator->trans('notification.read_All', domain: 'notification'),
            'read' => $this->translator->trans('notification.read', domain: 'notification'),
            'noRead' => $this->translator->trans('notification.no_read', domain: 'notification'),
            'deleteSelected' => $this->translator->trans('notification.delete_selected', domain: 'notification'),
            'deleteAll' => $this->translator->trans('notification.delete_all', domain: 'notification'),
            'all' => $this->translator->trans('notification.all', domain: 'notification'),
            'allSuccess' => $this->translator->trans('notification.all_success', domain: 'notification'),
            'statNonLu' => $this->translator->trans('notification.stat_non_lu', domain: 'notification'),
            'statToday' => $this->translator->trans('notification.stat_today', domain: 'notification'),
            'statAll' => $this->translator->trans('notification.stat_all', domain: 'notification'),
            'confirmTitle' => $this->translator->trans('notification.confirm.title', domain: 'notification'),
            'confirmBtnOK' => $this->translator->trans('notification.confirm.btn.ok', domain: 'notification'),
            'confirmBtnNo' => $this->translator->trans('notification.confirm.btn.no', domain: 'notification'),
            'confirmMsg' => $this->translator->trans('notification.confirm.msg', domain: 'notification'),
            'confirmSingleDelete' => $this->translator->trans(
                'notification.confirm.single_delete',
                domain: 'notification',
            ),
            'notification' => $this->getNotificationTranslate(),
        ];
    }

    private function getNotificationTranslate(): array
    {
        return [
            'seeComment' => $this->translator->trans('notification.notification.see.comment', domain: 'notification'),
            'read' => $this->translator->trans('notification.notification.read', domain: 'notification'),
            'time' => $this->translator->trans('notification.notification.time', domain: 'notification'),
        ];
    }
}
