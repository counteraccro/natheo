<?php

declare(strict_types=1);
/**
 * Paramètres de l'API pour la modération d'un commentaire
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api\Parameters\Content\Comment;

enum ApiModerateCommentParameter: string
{
    case STATUS = 'status';

    case MODERATION_COMMENT = 'moderation_comment';

    case USER_TOKEN = 'user_token';

    /**
     * Paramètres attendus
     */
    public const array PARAMS_REF = [
        self::STATUS->value => '',
        self::MODERATION_COMMENT->value => '',
        self::USER_TOKEN->value => '',
    ];

    /**
     * Valeurs par défaut des paramètres non renseignés
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::STATUS->value => '99999',
    ];
}
