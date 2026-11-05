<?php

declare(strict_types=1);
/**
 * Identifiants des jetons CSRF des actions d'écriture sur le compte de l'utilisateur connecté
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\User;

enum UserCsrfToken: string
{
    /**
     * Mise à jour d'une option de l'utilisateur
     * @var string
     */
    case UPDATE_MY_OPTION = 'user_update_my_option';

    /**
     * Désactivation de son propre compte
     * @var string
     */
    case SELF_DISABLED = 'user_self_disabled';

    /**
     * Suppression de son propre compte
     * @var string
     */
    case SELF_DELETE = 'user_self_delete';

    /**
     * Mise à jour d'une donnée de l'utilisateur (UserData)
     * @var string
     */
    case UPDATE_USER_DATA = 'user_update_user_data';
}
