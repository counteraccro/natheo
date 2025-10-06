<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Constantes globales pour les tests d'API
 */
namespace App\Utils\Api\Tests;

use App\Utils\Api\Parameters\ApiParametersUserAuthRef;
use App\Utils\System\User\Role;

class ApiUserAuthenticationTestConst
{
    /**
     * email du compte avec ROLE_USER
     * @var string
     */
    public const USERNAME_USER = 'user@natheo.com';

    /**
     * mot de passe du compte avec ROLE_USER
     * @var string
     */
    public const PASSWORD_USER = 'user@natheo.com';

    /**
     * email du compte avec ROLE_CONTRIBUTEUR
     * @var string
     */
    public const USERNAME_CONTRIBUTEUR = 'contributeur@natheo.com';

    /**
     * mot de passe du compte avec ROLE_CONTRIBUTEUR
     * @var string
     */
    public const PASSWORD_CONTRIBUTEUR = 'contributeur@natheo.com';

    /**
     * email du compte avec ROLE_ADMIN
     * @var string
     */
    public const USERNAME_ADMIN = 'admin@natheo.com';

    /**
     * mot de passe du compte avec ROLE_ADMIN
     * @var string
     */
    public const PASSWORD_ADMIN = 'admin@natheo.com';

    /**
     * email du compte avec ROLE_SUPER_ADMIN
     * @var string
     */
    public const USERNAME_SUPER_ADMIN = 'superadmin@natheo.com';

    /**
     * mot de passe du compte avec ROLE_SUPER_ADMIN
     * @var string
     */
    public const PASSWORD_SUPER_ADMIN = 'superadmin@natheo.com';

    /**
     * email du compte avec ROLE_FONDATEUR
     * @var string
     */
    public const USERNAME_FONDATEUR = 'user.demo@mail.fr';

    /**
     * mot de passe du compte avec ROLE_FONDATEUR
     * @var string
     */
    public const PASSWORD_FONDATEUR = 'user.demo@mail.fr';

    /**
     * Tableau des utilisateurs pour les test
     * @var array
     */
    public const AUTHENTICATION_USER_TAB = [
        Role::ROLE_USER => [
            ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_USERNAME => self::USERNAME_USER,
            ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_PASSWORD => self::PASSWORD_USER,
        ],
        Role::ROLE_CONTRIBUTEUR => [
            ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_USERNAME => self::USERNAME_CONTRIBUTEUR,
            ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_PASSWORD => self::PASSWORD_CONTRIBUTEUR,
        ],
        Role::ROLE_ADMIN => [
            ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_USERNAME => self::USERNAME_ADMIN,
            ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_PASSWORD => self::PASSWORD_ADMIN,
        ],
        Role::ROLE_SUPER_ADMIN => [
            ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_USERNAME => self::USERNAME_SUPER_ADMIN,
            ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_PASSWORD => self::PASSWORD_SUPER_ADMIN,
        ],
    ];
}
