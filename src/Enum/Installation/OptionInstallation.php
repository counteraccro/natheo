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
     * Option pour tester la connexion
     * @var string
     */
    case CONNEXION = 'connexion';

    /**
     * Test si la base de données existe
     * @var string
     */
    case DATABASE_EXIST = 'database_exist';
}
