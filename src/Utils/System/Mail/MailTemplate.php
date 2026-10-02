<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Class qui regroupe les paths des templates des emails
 */
namespace App\Utils\System\Mail;

class MailTemplate
{
    // Chemin Twig : toujours "/", indépendamment de l'OS
    private const string EMAIL_PATH = 'emails/';

    /**
     * Template email simple (contenu markdown converti en HTML)
     */
    public const string EMAIL_SIMPLE_TEMPLATE = self::EMAIL_PATH . 'simple_template.html.twig';
}
