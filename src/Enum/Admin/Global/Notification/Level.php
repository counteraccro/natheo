<?php

declare(strict_types=1);
/**
 * Niveau de notification
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Global\Notification;

enum Level: int
{
    case INFO = 1;
    case WARNING = 2;
    case ALERTE = 3;
}
