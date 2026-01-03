<?php
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
            KeyConfig::CATEGORY->value => Category::ADMIN->value,
            KeyConfig::LEVEL->value => Level::INFO->value,
            KeyConfig::PARAMETERS->value => [
                'login' => '',
                'role' => '',
                'url_aide' => '',
            ],
            KeyConfig::TITLE->value => 'notification.msg.welcome.title',
            KeyConfig::CONTENT->value => 'notification.msg.welcome.content',
        ],
        self::SELF_DISABLED->value => [
            KeyConfig::CATEGORY->value => Category::ADMIN->value,
            KeyConfig::LEVEL->value => Level::WARNING->value,
            KeyConfig::PARAMETERS->value => [
                'login' => '',
            ],
            KeyConfig::TITLE->value => 'notification.msg.self_disabled.title',
            KeyConfig::CONTENT->value => 'notification.msg.self_disabled.content',
        ],
        self::SELF_DELETE->value => [
            KeyConfig::CATEGORY->value => Category::ADMIN->value,
            KeyConfig::LEVEL->value => Level::WARNING->value,
            KeyConfig::PARAMETERS->value => [
                'login' => '',
            ],
            KeyConfig::TITLE->value => 'notification.msg.self_delete.title',
            KeyConfig::CONTENT->value => 'notification.msg.self_delete.content',
        ],
        self::SELF_ANONYMOUS->value => [
            KeyConfig::CATEGORY->value => Category::ADMIN->value,
            KeyConfig::LEVEL->value => Level::WARNING->value,
            KeyConfig::PARAMETERS->value => [
                'login' => '',
            ],
            KeyConfig::TITLE->value => 'notification.msg.self_anonymous.title',
            KeyConfig::CONTENT->value => 'notification.msg.self_anonymous.content',
        ],
        self::DUMP_SQL->value => [
            KeyConfig::CATEGORY->value => Category::SQL->value,
            KeyConfig::LEVEL->value => Level::INFO->value,
            KeyConfig::PARAMETERS->value => [
                'file' => '',
                'url' => '',
            ],
            KeyConfig::TITLE->value => 'notification.msg.dump_sql.title',
            KeyConfig::CONTENT->value => 'notification.msg.dump_sql.content',
        ],
        self::NEW_FONDATEUR->value => [
            KeyConfig::CATEGORY->value => Category::ADMIN->value,
            KeyConfig::LEVEL->value => Level::INFO->value,
            KeyConfig::PARAMETERS->value => [
                'login' => '',
                'url_aide' => '',
            ],
            KeyConfig::TITLE->value => 'notification.msg.new_fondateur.title',
            KeyConfig::CONTENT->value => 'notification.msg.new_fondateur.content',
        ],
        self::NEW_COMMENT->value => [
            KeyConfig::CATEGORY->value => Category::COMMENT->value,
            KeyConfig::LEVEL->value => Level::INFO->value,
            KeyConfig::PARAMETERS->value => [
                'author' => '',
                'status' => '',
                'page' => '',
                'id' => '',
            ],
            KeyConfig::TITLE->value => 'notification.msg.new_comment.title',
            KeyConfig::CONTENT->value => 'notification.msg.new_comment.content',
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
