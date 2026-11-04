<?php

declare(strict_types=1);
/**
 * Paramètres de l'authentification d'un utilisateur par l'API
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api\Parameters;

enum ApiUserAuthParameter: string
{
    case USERNAME = 'username';

    case PASSWORD = 'password';

    /**
     * Paramètres attendus et leur type
     */
    public const array PARAMS_REF = [
        self::USERNAME->value => 'string',
        self::PASSWORD->value => 'string',
    ];
}
