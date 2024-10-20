<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Référence pour les paramètres attendus pour l'API find page
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
     * Paramètre page
     * @var string
     */
    public const PARAM_PAGE = 'page';

    /**
     * Paramètre limite
     * @var string
     */
    public const PARAM_LIMIT = 'limit';

    /**
     * Paramètre show_menu
     * @var string
     */
    public const PARAM_SHOW_MENU = 'show_menu';

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
        self::PARAM_PAGE => '',
        self::PARAM_LIMIT => '',
        self::PARAM_SHOW_MENU => '',
        self::PARAM_MENU_POSITION => '',
        self::PARAM_USER_TOKEN => '',
    ];

    /**
     * Tableau valeur par défaut
     */
    public const PARAMS_DEFAULT_VALUE = [
        self::PARAM_LOCALE => 'fr',
        self::PARAM_PAGE => 1,
        self::PARAM_LIMIT => 25,
        self::PARAM_SHOW_MENU => true,
        self::PARAM_MENU_POSITION => [0 => 0],
    ];
}
