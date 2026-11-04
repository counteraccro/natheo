<?php

declare(strict_types=1);
/**
 * Types de messages flash
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Global;

enum FlashKey: string
{
    case SUCCESS = 'success';

    case WARNING = 'warning';

    case DANGER = 'danger';
}
