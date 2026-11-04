<?php

declare(strict_types=1);
/**
 * Paramètres de l'API pour la recherche d'un menu
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api\Parameters\Content\Menu;

enum ApiFindMenuParameter: string
{
    case ID = 'id';

    case PAGE_SLUG = 'page_slug';

    case POSITION = 'position';

    case LOCALE = 'locale';

    case USER_TOKEN = 'user_token';

    /**
     * Paramètres attendus
     */
    public const array PARAMS_REF = [
        self::ID->value => '',
        self::PAGE_SLUG->value => '',
        self::POSITION->value => '',
        self::LOCALE->value => '',
        self::USER_TOKEN->value => '',
    ];

    /**
     * Valeurs par défaut des paramètres non renseignés
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::POSITION->value => 1,
        self::LOCALE->value => 'fr',
    ];
}
