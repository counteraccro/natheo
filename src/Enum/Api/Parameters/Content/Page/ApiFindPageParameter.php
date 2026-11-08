<?php

declare(strict_types=1);
/**
 * Paramètres de l'API pour la recherche d'une page
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api\Parameters\Content\Page;

enum ApiFindPageParameter: string
{
    case SLUG = 'slug';

    case LOCALE = 'locale';

    case SHOW_MENUS = 'show_menus';

    case SHOW_TAGS = 'show_tags';

    case SHOW_STATISTIQUES = 'show_statistiques';

    case MENU_POSITION = 'menu_positions';

    case USER_TOKEN = 'user_token';

    case PREVIEW = 'preview';

    /**
     * Paramètres attendus
     */
    public const array PARAMS_REF = [
        self::SLUG->value => '',
        self::LOCALE->value => '',
        self::SHOW_MENUS->value => '',
        self::SHOW_TAGS->value => '',
        self::SHOW_STATISTIQUES->value => '',
        self::MENU_POSITION->value => '',
        self::USER_TOKEN->value => '',
        self::PREVIEW->value => '',
    ];

    /**
     * Valeurs par défaut des paramètres non renseignés
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::LOCALE->value => 'fr',
        self::SHOW_MENUS->value => true,
        self::SHOW_TAGS->value => true,
        self::SHOW_STATISTIQUES->value => true,
        self::MENU_POSITION->value => [0 => 0],
        self::PREVIEW->value => false,
    ];
}
