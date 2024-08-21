<?php

namespace App\Utils\Installation;

class InstallationConst
{
    /**
     * Secret par défaut à changer
     */
    const DEFAULT_SECRET = '93df0a91243d55a4d72a801726b38645';

    /**
     * Option pour construire le DATABASE_URL <br />
     * donne type://login:!password![a]ip:port
     * @var string
     */
    const OPTION_DATABASE_URL_TEST = 'test_connexion';

    /**
     * Option pour construire le DATABASE_URL <br />
     * donne type://login:!password![a]ip:port/database?serverVersion=version&charset=charset
     * @var string
     */
    const OPTION_DATABASE_URL_CREATE_DATABASE = 'create_database';
}
