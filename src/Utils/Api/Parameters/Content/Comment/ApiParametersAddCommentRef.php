<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Référence pour les paramètres attendu ApiAddCommet
 */

namespace App\Utils\Api\Parameters\Content\Comment;

class ApiParametersAddCommentRef
{
    /**
     * Paramètre id
     * @var string
     */
    public const string PARAM_ID = 'page_id';

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
     * Paramètre auteur
     * @var string
     */
    public const string PARAM_AUTHOR = 'author';

    /**
     * Paramètre email
     * @var string
     */
    public const string PARAM_EMAIL = 'email';

    /**
     * Paramètre comment
     * @var string
     */
    public const string PARAM_COMMENT = 'comment';

    /**
     * Paramètre ip
     * @var string
     */
    public const string PARAM_IP = 'ip';

    /**
     * Paramètre user-agent
     * @var string
     */
    public const string PARAM_USER_AGENT = 'user_agent';

    /**
     * Tableau des paramètres
     * @var array
     */
    public const array PARAMS_REF = [
        self::PARAM_ID => '',
        self::PARAM_PAGE_SLUG => '',
        self::PARAM_LOCALE => '',
        self::PARAM_AUTHOR => '',
        self::PARAM_EMAIL => '',
        self::PARAM_COMMENT => '',
        self::PARAM_IP => '',
        self::PARAM_USER_AGENT => '',
    ];

    /**
     * Tableau valeur par défaut
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::PARAM_LOCALE => 'fr',
    ];
}
