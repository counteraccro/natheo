<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Installation;

enum KeyEnv: string
{
    /**
     * Clé DATABASE_URL
     * @var string
     */
    case DATABASE_URL = 'DATABASE_URL';

    /**
     * Clé APP_SECRET
     * @var string
     */
    case APP_SECRET = 'APP_SECRET';

    /**
     * Clé APP_ENV
     * @var string
     */
    case APP_ENV = 'APP_ENV';

    /**
     * Clé NATHEO_DBNAME
     * @var string
     */
    case NATHEO_DBNAME = 'NATHEO_DBNAME';
}
