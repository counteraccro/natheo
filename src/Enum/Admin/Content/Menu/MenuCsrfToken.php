<?php

declare(strict_types=1);
/**
 * Identifiants des jetons CSRF des actions d'écriture sur les menus
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Menu;

enum MenuCsrfToken: string
{
    /**
     * Suppression d'un menu
     * @var string
     */
    case DELETE = 'menu_delete';

    /**
     * Activation / désactivation d'un menu
     * @var string
     */
    case DISABLED = 'menu_update_disabled';

    /**
     * Passage d'un menu en menu par défaut
     * @var string
     */
    case SWITCH_DEFAULT = 'menu_switch_default';

    /**
     * Sauvegarde d'un menu
     * @var string
     */
    case SAVE = 'menu_save';
}
