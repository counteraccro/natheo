<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paramètre de référence remonté un PageContent
 */
namespace App\Utils\Api\Parameters\Content\Page;

class ApiParametersFindPageContentRef
{
    /**
     * Paramètre id
     * @var string
     */
    public const PARAM_ID = 'id';

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
        self::PARAM_LOCALE => '',
        self::PARAM_PAGE => '',
        self::PARAM_LIMIT => '',
        self::PARAM_USER_TOKEN => '',
    ];

    /**
     * Tableau valeur par défaut
     */
    public const PARAMS_DEFAULT_VALUE = [
        self::PARAM_LOCALE => 'fr',
        self::PARAM_PAGE => 1,
        self::PARAM_LIMIT => 25,
    ];

}
