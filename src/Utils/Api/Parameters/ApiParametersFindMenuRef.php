<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Référence pour les paramètres attendus pour l'API find menu
 */
namespace App\Utils\Api\Parameters;

class ApiParametersFindMenuRef
{
    /**
     * Paramètre id
     * @var string
     */
    public const PARAM_REF_FIND_MENU_ID = 'id';

    /**
     * Paramètre page-slug
     * @var string
     */
    public const PARAM_REF_FIND_MENU_SLUG = 'page_slug';

    /**
     * Paramètre position
     * @var string
     */
    public const PARAM_REF_FIND_MENU_POSITION = 'position';

    /**
     * Paramètre locale
     * @var string
     */
    public const PARAM_REF_FIND_MENU_LOCALE = 'locale';

    /**
     * Paramètre user_token
     * @var string
     */
    public const PARAM_REF_FIND_MENU_USER_TOKEN = 'user_token';

    /**
     * Tableau des paramètres
     * @var array
     */
    public const PARAMS_REF_FIND_MENU = [
        self::PARAM_REF_FIND_MENU_ID => '',
        self::PARAM_REF_FIND_MENU_SLUG => '',
        self::PARAM_REF_FIND_MENU_POSITION => '',
        self::PARAM_REF_FIND_MENU_LOCALE => '',
        self::PARAM_REF_FIND_MENU_USER_TOKEN => '',
    ];

    /**
     * Tableau valeur par défaut
     */
    public const PARAMS_REF_FIND_MENU_DEFAULT_VALUE =[
        self::PARAM_REF_FIND_MENU_POSITION => 1,
        self::PARAM_REF_FIND_MENU_LOCALE => 'fr',
    ];
}
