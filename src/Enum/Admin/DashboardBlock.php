<?php
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
}
