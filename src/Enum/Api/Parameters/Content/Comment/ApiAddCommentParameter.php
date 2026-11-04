<?php

declare(strict_types=1);
/**
 * Paramètres de l'API pour l'ajout d'un commentaire
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api\Parameters\Content\Comment;

enum ApiAddCommentParameter: string
{
    case ID = 'page_id';

    case PAGE_SLUG = 'page_slug';

    case LOCALE = 'locale';

    case AUTHOR = 'author';

    case EMAIL = 'email';

    case COMMENT = 'comment';

    case IP = 'ip';

    case USER_AGENT = 'user_agent';

    /**
     * Paramètres attendus
     */
    public const array PARAMS_REF = [
        self::ID->value => '',
        self::PAGE_SLUG->value => '',
        self::LOCALE->value => '',
        self::AUTHOR->value => '',
        self::EMAIL->value => '',
        self::COMMENT->value => '',
        self::IP->value => '',
        self::USER_AGENT->value => '',
    ];

    /**
     * Valeurs par défaut des paramètres non renseignés
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::LOCALE->value => 'fr',
    ];
}
