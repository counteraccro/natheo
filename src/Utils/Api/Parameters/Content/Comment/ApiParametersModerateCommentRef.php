<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Référence pour les paramètres attendu ApiAddCommet
 */

namespace App\Utils\Api\Parameters\Content\Comment;

use App\Utils\Content\Comment\CommentConst;

class ApiParametersModerateCommentRef
{
    /**
     * Paramètre id
     * @var string
     */
    public const string PARAM_STATUS = 'status';

    /**
     * Paramètre page-slug
     * @var string
     */
    public const string PARAM_MODERATION_COMMENT = 'moderation_comment';

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
        self::PARAM_STATUS => '',
        self::PARAM_MODERATION_COMMENT => '',
        self::PARAM_USER_TOKEN => '',
    ];

    /**
     * Tableau valeur par défaut
     */
    public const array PARAMS_DEFAULT_VALUE = [
        self::PARAM_STATUS => '99999',
    ];
}
