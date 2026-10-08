<?php

declare(strict_types=1);
/**
 * Liste des clés pour les UserData
 * @author Gourdon Aymeric
 * @version 2.0
 */

namespace App\Enum\Admin\System\User;

enum UserDataKey: string
{
    /**
     * Clé pour le reset de mot de passe
     * @var string
     */
    case RESET_PASSWORD = 'KEY_RESET_PASSWORD';

    /**
     * Clé pour la date de dernière connexion
     * @var string
     */
    case LAST_CONNEXION = 'KEY_LAST_CONNEXION';

    /**
     * Clé pour définir si on lance l'aide ou non à la première connexion
     * @var string
     */
    case HELP_FIRST_CONNEXION = 'KEY_HELP_FIRST_CONNEXION';

    /**
     * Clé pour définir le token de connexion
     * @var string
     */
    case TOKEN_CONNEXION = 'KEY_TOKEN_CONNEXION';

    /**
     * Clé pour définir le temps de validité du token
     * @var string
     */
    case TIME_VALIDATE_TOKEN = 'TIME_VALIDATE_TOKEN';
}
