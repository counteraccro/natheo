<?php

declare(strict_types=1);
/**
 * Identifiants des jetons CSRF des actions d'écriture sur les notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Global\Notification;

enum NotificationCsrfToken: string
{
    /**
     * Passage en lu / non lu d'une ou plusieurs notifications
     * @var string
     */
    case UPDATE = 'notification_update';

    /**
     * Suppression d'une ou plusieurs notifications
     * @var string
     */
    case DELETE = 'notification_delete';

    /**
     * Purge des anciennes notifications
     * @var string
     */
    case PURGE = 'notification_purge';

    /**
     * Passage en lu de toutes les notifications
     * @var string
     */
    case READ_ALL = 'notification_read_all';
}
