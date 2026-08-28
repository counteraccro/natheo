<?php

declare(strict_types=1);
/**
 * Enum sur les environnements symfony
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Installation;

enum Env: string
{
    /**
     * Prod mode
     * @var string
     */
    case PROD = 'prod';

    /**
     * Dev mode
     * @var string
     */
    case DEV = 'dev';
}
