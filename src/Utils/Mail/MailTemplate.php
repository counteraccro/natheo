<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class qui regroupe les paths des templates des emails
 */
namespace App\Utils\Mail;

class MailTemplate
{
    /**
     * Path du dossier user pour les emails
     */
    private const EMAIL_USER_PATH = 'emails' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR;

    /**
     * Template email nouveau mot de passe
     */
    public const EMAIL_USER_NEW_PASSWORD = self::EMAIL_USER_PATH . 'new_password.html.twig';
}
