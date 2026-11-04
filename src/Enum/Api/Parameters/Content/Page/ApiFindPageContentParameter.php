<?php

declare(strict_types=1);
/**
 * Paramètres de l'API pour la liste des pages d'un contenu
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api\Parameters\Content\Page;

enum ApiFindPageContentParameter: string
{
    case ID = 'id';

    case LOCALE = 'locale';

    case PAGE = 'page';

    case LIMIT = 'limit';

    case USER_TOKEN = 'user_token';

    /**
     * Paramètres attendus
     */
    public const array PARAMS_REF = [
        self::ID->value => '',
        self::LOCALE->value => '',
        self::PAGE->value => '',
        self::LIMIT->value => '',
        self::USER_TOKEN->value => '',
    ];

    /**
     * Valeurs par défaut des paramètres non renseignés
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::LOCALE->value => 'fr',
        self::PAGE->value => 1,
        self::LIMIT->value => 25,
    ];
}
