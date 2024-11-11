<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Référence pour les paramètres attendus pour l'API find page
 */
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paramètre de référence pour la recherche d'une page
 */
namespace App\Utils\Api\Parameters\Content\Page;

class ApiParametersFindPageRef
{
    /**
     * Paramètre slug
     * @var string
     */
    public const PARAM_SLUG = 'slug';

    /**
     * Paramètre locale
     * @var string
     */
    public const PARAM_LOCALE = 'locale';

    /**
     * Paramètre show_menu
     * @var string
     */
    public const PARAM_SHOW_MENUS = 'show_menus';

    /**
     * Paramètre show_menu
     * @var string
     */
    public const PARAM_SHOW_TAGS = 'show_tags';

    /**
     * Paramètre show_menu
     * @var string
     */
    public const PARAM_SHOW_STATISTIQUES = 'show_statistiques';

    /**
     * Paramètre menu_position
     * @var string
     */
    public const PARAM_MENU_POSITION = 'menu_positions';

    /**
     * Paramètre user_token
     * @var string
     */
    public const PARAM_USER_TOKEN = 'user_token';

    /**
     * Tableau des paramètres
     * @var array
     */
    public const PARAMS_REF = [
        self::PARAM_SLUG => '',
        self::PARAM_LOCALE => '',
        self::PARAM_SHOW_MENUS => '',
        self::PARAM_SHOW_TAGS => '',
        self::PARAM_SHOW_STATISTIQUES => '',
        self::PARAM_MENU_POSITION => '',
        self::PARAM_USER_TOKEN => '',
    ];

    /**
     * Tableau valeur par défaut
     */
    public const PARAMS_DEFAULT_VALUE = [
        self::PARAM_LOCALE => 'fr',
        self::PARAM_SHOW_MENUS => true,
        self::PARAM_SHOW_TAGS => true,
        self::PARAM_SHOW_STATISTIQUES => true,
        self::PARAM_MENU_POSITION => [0 => 0],
    ];
}
