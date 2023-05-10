<?php
/**
 * Clé des notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Notification;

class NotificationKey
{
    /**
     * Niveau info
     * @const
     */
    const LEVEL_INFO = '1';

    /**
     * Niveau warning
     * @const
     */
    const LEVEL_WARNING = '2';

    /**
     * Niveau danger
     * @const
     */
    const LEVEL_DANGER = '3';

    /**
     * Clé paramètres
     * @const
     */
    const KEY_PARAMETER = 'parameters';

    /**
     * Clé title
     * @const
     */
    const KEY_TITLE = 'title';

    /**
     * Clé content
     * @const
     */
    const KEY_CONTENT = 'content';

    /**
     * Clé level
     * @const
     */
    const KEY_LEVEL = 'level';

    /**
     * Notification bienvenue
     */
    const KEY_NOTIF_WELCOME = 'NOTIF_WELCOME';

    /**
     * Tableau de notification
     */
    const TAB_NOTIFICATIONS = [
        self::KEY_NOTIF_WELCOME => [
            self::KEY_PARAMETER => [
                'login' => '',
                'role' => ''
            ],
            self::KEY_TITLE => 'notification.welcome.title',
            self::KEY_CONTENT => 'notification.welcome.content',
            self::KEY_LEVEL => self::LEVEL_INFO
        ]
    ];
}
