<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Référence pour les paramètres attendus
 */
namespace App\Utils\Api\Parameters;

class ApiParametersUserAuthRef
{

    /**
     * Type String
     * @var string
     */
    public const TYPE_STRING = 'string';

    /**
     * Paramètres de référence pour l'authentification du user
     * username
     * @var string
     */
    public const PARAMS_REF_AUTH_USER_USERNAME = 'username';

    /**
     * Paramètres de référence pour l'authentification du user
     * password
     * @var string
     */
    public const PARAMS_REF_AUTH_USER_PASSWORD = 'password';

    /**
     * Paramètres de référence pour l'authentification du user
     * @var array
     */
    public const PARAMS_REF_AUTH_USER = [
        self::PARAMS_REF_AUTH_USER_USERNAME => self::TYPE_STRING,
        self::PARAMS_REF_AUTH_USER_PASSWORD => self::TYPE_STRING
    ];
}
