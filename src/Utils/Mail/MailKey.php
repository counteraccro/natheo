<?php
/**
 * Liste des clés d'identification des emails
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Mail;

class MailKey
{
    /**
     * Cle pour le mail de changement de mot de passe
     * @const
     */
    public const MAIL_CHANGE_PASSWORD = 'MAIL_CHANGE_PASSWORD';

    /**
     * Clé pour le mail du reset de mot de passe
     */
    public const MAIL_RESET_PASSWORD = 'MAIL_RESET_PASSWORD';

    /**
     * Clé pour le mail de désactivation d'un compte user
     * @const
     */
    public const MAIL_ACCOUNT_ADM_DISABLE = 'MAIL_ACCOUNT_ADM_DISABLE';

    /**
     * Clé pour le mail de réactivation d'un compte user
     * @const
     */
    public const MAIL_ACCOUNT_ADM_ENABLE = 'MAIL_ACCOUNT_ADM_ENABLE';

    /**
     * Clé pour le mail de création d'un compte user
     * @const
     */
    public const MAIL_CREATE_ACCOUNT_ADM = 'MAIL_CREATE_ACCOUNT_ADM';

    /**
     * Clé pour le mail d'auto désactivation du compte
     */
    public const MAIL_SELF_DISABLED_ACCOUNT = 'MAIL_SELF_DISABLED_ACCOUNT';

    /**
     * Clé pour le mail d'auto suppression du compte
     */
    public const MAIL_SELF_DELETE_ACCOUNT = 'MAIL_SELF_DELETE_ACCOUNT';

    /**
     * Clé pour le mail d'auto anonymisation du compte
     */
    public const MAIL_SELF_ANONYMOUS_ACCOUNT = 'MAIL_SELF_ANONYMOUS_ACCOUNT';
}
