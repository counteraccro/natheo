<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Global\Notification;

enum Notification: string
{
    /**
     * Notification bienvenue
     * @const
     */
    case WELCOME = 'NOTIFICATION_WELCOME';

    /**
     * Notification désactivation par l'utilisateur lui-même
     * @const
     */
    case SELF_DISABLED = 'NOTIFICATION_SELF_DISABLED';

    /**
     * Notification suppression par l'utilisateur lui-même
     * @const
     */
    case SELF_DELETE = 'NOTIFICATION_SELF_DELETE';

    /**
     * Notification anonymisation par l'utilisateur lui-même
     * @const
     */
    case SELF_ANONYMOUS = 'NOTIFICATION_SELF_ANONYMOUS';

    /**
     * Notification lors de la création d'un nouveau dump SQL
     * @const
     */
    case DUMP_SQL = 'NOTIFICATION_DUMP_SQL';

    /**
     * Notification lors de la création d'un nouveau fondateur
     */
    case NEW_FONDATEUR = 'NOTIFICATION_NEW_FONDATEUR';

    /**
     * Nouveau commentaire
     */
    case NEW_COMMENT = 'new_comment';

    private const CONFIG = [
        self::WELCOME->value => [
            NotificationKeyConfig::CATEGORY->value => NotificationCategory::ADMIN->value,
            NotificationKeyConfig::LEVEL->value => NotificationLevel::INFO->value,
            NotificationKeyConfig::PARAMETERS->value => [
                'login' => '',
                'role' => '',
                'url_aide' => '',
            ],
            NotificationKeyConfig::TITLE->value => 'notification.msg.welcome.title',
            NotificationKeyConfig::CONTENT->value => 'notification.msg.welcome.content',
        ],
        self::SELF_DISABLED->value => [
            NotificationKeyConfig::CATEGORY->value => NotificationCategory::ADMIN->value,
            NotificationKeyConfig::LEVEL->value => NotificationLevel::WARNING->value,
            NotificationKeyConfig::PARAMETERS->value => [
                'login' => '',
            ],
            NotificationKeyConfig::TITLE->value => 'notification.msg.self_disabled.title',
            NotificationKeyConfig::CONTENT->value => 'notification.msg.self_disabled.content',
        ],
        self::SELF_DELETE->value => [
            NotificationKeyConfig::CATEGORY->value => NotificationCategory::ADMIN->value,
            NotificationKeyConfig::LEVEL->value => NotificationLevel::WARNING->value,
            NotificationKeyConfig::PARAMETERS->value => [
                'login' => '',
            ],
            NotificationKeyConfig::TITLE->value => 'notification.msg.self_delete.title',
            NotificationKeyConfig::CONTENT->value => 'notification.msg.self_delete.content',
        ],
        self::SELF_ANONYMOUS->value => [
            NotificationKeyConfig::CATEGORY->value => NotificationCategory::ADMIN->value,
            NotificationKeyConfig::LEVEL->value => NotificationLevel::WARNING->value,
            NotificationKeyConfig::PARAMETERS->value => [
                'login' => '',
            ],
            NotificationKeyConfig::TITLE->value => 'notification.msg.self_anonymous.title',
            NotificationKeyConfig::CONTENT->value => 'notification.msg.self_anonymous.content',
        ],
        self::DUMP_SQL->value => [
            NotificationKeyConfig::CATEGORY->value => NotificationCategory::SQL->value,
            NotificationKeyConfig::LEVEL->value => NotificationLevel::INFO->value,
            NotificationKeyConfig::PARAMETERS->value => [
                'file' => '',
                'url' => '',
            ],
            NotificationKeyConfig::TITLE->value => 'notification.msg.dump_sql.title',
            NotificationKeyConfig::CONTENT->value => 'notification.msg.dump_sql.content',
        ],
        self::NEW_FONDATEUR->value => [
            NotificationKeyConfig::CATEGORY->value => NotificationCategory::ADMIN->value,
            NotificationKeyConfig::LEVEL->value => NotificationLevel::INFO->value,
            NotificationKeyConfig::PARAMETERS->value => [
                'login' => '',
                'url_aide' => '',
            ],
            NotificationKeyConfig::TITLE->value => 'notification.msg.new_fondateur.title',
            NotificationKeyConfig::CONTENT->value => 'notification.msg.new_fondateur.content',
        ],
        self::NEW_COMMENT->value => [
            NotificationKeyConfig::CATEGORY->value => NotificationCategory::COMMENT->value,
            NotificationKeyConfig::LEVEL->value => NotificationLevel::INFO->value,
            NotificationKeyConfig::PARAMETERS->value => [
                'author' => '',
                'status' => '',
                'page' => '',
                'id' => '',
            ],
            NotificationKeyConfig::TITLE->value => 'notification.msg.new_comment.title',
            NotificationKeyConfig::CONTENT->value => 'notification.msg.new_comment.content',
        ],
    ];

    /**
     * Retourne une notification
     * @param string $key
     * @return array
     */
    public static function getNotification(string $key): array
    {
        return self::CONFIG[$key];
    }

    /**
     * Test si une notification existe
     * @param string $key
     * @return bool
     */
    public static function isExist(string $key): bool
    {
        return isset(self::CONFIG[$key]);
    }
}
