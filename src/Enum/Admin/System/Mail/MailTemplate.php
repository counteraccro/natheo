<?php

declare(strict_types=1);
/**
 * Templates Twig des emails (chemin Twig : toujours « / », indépendamment de l'OS)
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\System\Mail;

enum MailTemplate: string
{
    case SIMPLE = 'emails/simple_template.html.twig';
}
