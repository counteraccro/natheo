<?php

declare(strict_types=1);
/**
 * Statut d'activation d'un élément d'administration, filtre « status » des collections
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api\Admin;

use App\Enum\Trait\EnumSlugTrait;

enum ActivationStatus: string
{
    use EnumSlugTrait;

    case ENABLED = 'enabled';

    case DISABLED = 'disabled';
}
