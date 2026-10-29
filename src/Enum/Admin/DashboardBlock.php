<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin;

enum DashboardBlock: string
{
    case HELP_FIRST_CONNEXION = 'help_first_connexion';

    case LAST_COMMENT = 'last_comment';

    case LAST_PAGE_CREATE = 'last_page';

    case PAGES_MOST_VIEWED = 'pages_most_viewed';

    /**
     * Retourne le rôle minimum pour accéder au block
     * @return string
     */
    public function getRole(): string
    {
        return match ($this) {
            self::HELP_FIRST_CONNEXION => 'ROLE_SUPER_ADMIN',
            self::LAST_COMMENT, self::LAST_PAGE_CREATE, self::PAGES_MOST_VIEWED => 'ROLE_CONTRIBUTEUR',
        };
    }
}
