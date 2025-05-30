<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Référence pour les paramètres attendu ApiCommentByPage
 */

namespace App\Utils\Api\Parameters\Content\Comment;

class ApiParametersCommentByPageRef
{
    /**
     * Paramètre id
     * @var string
     */
    public const string PARAM_ID = 'id';

    /**
     * Paramètre page-slug
     * @var string
     */
    public const string PARAM_PAGE_SLUG = 'page_slug';

    /**
     * Paramètre locale
     * @var string
     */
    public const string PARAM_LOCALE = 'locale';

    /**
     * Paramètre page
     * @var string
     */
    public const string PARAM_PAGE = 'page';

    /**
     * Paramètre limite
     * @var string
     */
    public const string PARAM_LIMIT = 'limit';


    /**
     * Paramètre order_by
     * @var string
     */
    public const string PARAM_ORDER_BY = 'order_by';

    /**
     * Paramètre order
     * @var string
     */
    public const string PARAM_ORDER = 'order';

    /**
     * Paramètre user_token
     * @var string
     */
    public const string PARAM_USER_TOKEN = 'user_token';

    /**
     * Tableau des paramètres
     * @var array
     */
    public const array PARAMS_REF = [
        self::PARAM_ID => '',
        self::PARAM_PAGE_SLUG => '',
        self::PARAM_LOCALE => '',
        self::PARAM_PAGE => '',
        self::PARAM_LIMIT => '',
        self::PARAM_ORDER => '',
        self::PARAM_ORDER_BY => '',
        self::PARAM_USER_TOKEN => '',
    ];

    /**
     * Tableau valeur par défaut
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::PARAM_LOCALE => 'fr',
        self::PARAM_PAGE => 1,
        self::PARAM_LIMIT => 25,
        self::PARAM_ORDER => 'desc',
        self::PARAM_ORDER_BY => 'createdAt',
    ];
}
