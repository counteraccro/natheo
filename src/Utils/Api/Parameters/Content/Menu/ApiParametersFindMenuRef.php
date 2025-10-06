<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Référence pour les paramètres attendus pour l'API find menu
 */
namespace App\Utils\Api\Parameters\Content\Menu;

class ApiParametersFindMenuRef
{
    /**
     * Paramètre id
     * @var string
     */
    public const PARAM_ID = 'id';

    /**
     * Paramètre page-slug
     * @var string
     */
    public const PARAM_PAGE_SLUG = 'page_slug';

    /**
     * Paramètre position
     * @var string
     */
    public const PARAM_POSITION = 'position';

    /**
     * Paramètre locale
     * @var string
     */
    public const PARAM_LOCALE = 'locale';

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
        self::PARAM_ID => '',
        self::PARAM_PAGE_SLUG => '',
        self::PARAM_POSITION => '',
        self::PARAM_LOCALE => '',
        self::PARAM_USER_TOKEN => '',
    ];

    /**
     * Tableau valeur par défaut
     */
    public const PARAMS_REF_DEFAULT_VALUE = [
        self::PARAM_POSITION => 1,
        self::PARAM_LOCALE => 'fr',
    ];
}
