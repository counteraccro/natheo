<?php

declare(strict_types=1);
/**
 * Paramètres de l'API pour la liste des commentaires d'une page
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api\Parameters\Content\Comment;

enum ApiCommentByPageParameter: string
{
    case ID = 'id';

    case PAGE_SLUG = 'page_slug';

    case LOCALE = 'locale';

    case PAGE = 'page';

    case LIMIT = 'limit';

    case ORDER_BY = 'order_by';

    case ORDER = 'order';

    case USER_TOKEN = 'user_token';

    /**
     * Paramètres attendus
     */
    public const array PARAMS_REF = [
        self::ID->value => '',
        self::PAGE_SLUG->value => '',
        self::LOCALE->value => '',
        self::PAGE->value => '',
        self::LIMIT->value => '',
        self::ORDER->value => '',
        self::ORDER_BY->value => '',
        self::USER_TOKEN->value => '',
    ];

    /**
     * Valeurs par défaut des paramètres non renseignés
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::LOCALE->value => 'fr',
        self::PAGE->value => 1,
        self::LIMIT->value => 25,
        self::ORDER->value => 'desc',
        self::ORDER_BY->value => 'createdAt',
    ];
}
