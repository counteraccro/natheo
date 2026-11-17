<?php

declare(strict_types=1);
/**
 * Type d'un token d'API, qui détermine les opérations accessibles
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\ApiToken;

enum ApiTokenScope: string
{
    /**
     * Token du site public et des intégrations, limité aux opérations publiques de l'API
     */
    case PUBLIC = 'public';

    /**
     * Token du back-office, qui ouvre les sessions utilisateur des opérations d'administration
     */
    case BACK_OFFICE = 'back_office';

    /**
     * Rôle donné à l'utilisateur de sécurité de l'API selon le type du token
     * @return string
     */
    public function getRole(): string
    {
        return match ($this) {
            self::PUBLIC => 'ROLE_API_PUBLIC',
            self::BACK_OFFICE => 'ROLE_API_BACK_OFFICE',
        };
    }
}
