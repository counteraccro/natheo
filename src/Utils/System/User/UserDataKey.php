<?php
/**
 * @author Gourdon Aymeric
 * @version 1.3
 * Clé pour userdata
 */

namespace App\Utils\System\User;

class UserDataKey
{
    /**
     * Clé pour le reset de mot de passe
     * @var string
     */
    const KEY_RESET_PASSWORD = 'KEY_RESET_PASSWORD';

    /**
     * Clé pour la date de dernière connexion
     * @var string
     */
    const KEY_LAST_CONNEXION = 'KEY_LAST_CONNEXION';

    /**
     * Clé pour définir si on lance l'aide ou non à la première connexion
     * @var string
     */
    const KEY_HELP_FIRST_CONNEXION = 'KEY_HELP_FIRST_CONNEXION';

    /**
     * Clé pour définir le token de connexion
     * @var string
     */
    const KEY_TOKEN_CONNEXION = 'KEY_TOKEN_CONNEXION';

    /**
     * Clé pour définir le temps de validité du token
     * @var string
     */
    const TIME_VALIDATE_TOKEN = 'TIME_VALIDATE_TOKEN';
}
