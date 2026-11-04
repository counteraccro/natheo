<?php

declare(strict_types=1);
/**
 * Statut retourné dans les réponses de l'API
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Api;

enum ApiMessage: string
{
    case SUCCESS = 'success';

    case ERROR = 'error';
}
