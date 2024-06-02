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
     * @const
     */
    const NOTIFICATION_WELCOME = 'NOTIFICATION_WELCOME';

    /**
     * Notification désactivation par l'utilisateur lui-même
     * @const
     */
    const NOTIFICATION_SELF_DISABLED = 'NOTIFICATION_SELF_DISABLED';

    /**
     * Notification suppression par l'utilisateur lui-même
     * @const
     */
    const NOTIFICATION_SELF_DELETE = 'NOTIFICATION_SELF_DELETE';

    /**
     * Notification anonymisation par l'utilisateur lui-même
     * @const
     */
    const NOTIFICATION_SELF_ANONYMOUS = 'NOTIFICATION_SELF_ANONYMOUS';

    /**
     * Notification lors de la création d'un nouveau dump SQL
     * @const
     */
    const NOTIFICATION_DUMP_SQL = 'NOTIFICATION_DUMP_SQL';

    /**
     * Tableau de notification
     */
    const TAB_NOTIFICATIONS = [
        self::NOTIFICATION_WELCOME => [
            self::KEY_PARAMETER => [
                'login' => '',
                'role' => '',
                'url_aide' => ''
            ],
            self::KEY_TITLE => 'notification.msg.welcome.title',
            self::KEY_CONTENT => 'notification.msg.welcome.content',
            self::KEY_LEVEL => self::LEVEL_INFO
        ],
        self::NOTIFICATION_SELF_DISABLED => [
            self::KEY_PARAMETER => [
                'login' => '',
            ],
            self::KEY_TITLE => 'notification.msg.self_disabled.title',
            self::KEY_CONTENT => 'notification.msg.self_disabled.content',
            self::KEY_LEVEL => self::LEVEL_WARNING
        ],
        self::NOTIFICATION_SELF_DELETE => [
            self::KEY_PARAMETER => [
                'login' => '',
            ],
            self::KEY_TITLE => 'notification.msg.self_delete.title',
            self::KEY_CONTENT => 'notification.msg.self_delete.content',
            self::KEY_LEVEL => self::LEVEL_WARNING
        ],
        self::NOTIFICATION_SELF_ANONYMOUS => [
            self::KEY_PARAMETER => [
                'login' => '',
            ],
            self::KEY_TITLE => 'notification.msg.self_anonymous.title',
            self::KEY_CONTENT => 'notification.msg.self_anonymous.content',
            self::KEY_LEVEL => self::LEVEL_WARNING
        ],
        self::NOTIFICATION_DUMP_SQL => [
            self::KEY_PARAMETER => [
                'file' => '',
                'url' => ''
            ],
            self::KEY_TITLE => 'notification.msg.dump_sql.title',
            self::KEY_CONTENT => 'notification.msg.dump_sql.content',
            self::KEY_LEVEL => self::LEVEL_INFO
        ]
    ];
}
