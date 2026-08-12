<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Installation;

enum OptionInstallation: string
{
    /**
     * Option pour construire le DATABASE_URL <br />
     * donne type://login:!password![a]ip:port
     * @var string
     */
    case DATABASE_URL_TEST = 'test_connexion';

    /**
     * Option pour construire le DATABASE_URL <br />
     * donne type://login:!password![a]ip:port/database?serverVersion=version&charset=charset
     * @var string
     */
    case DATABASE_URL_CREATE_DATABASE = 'create_database';
}
