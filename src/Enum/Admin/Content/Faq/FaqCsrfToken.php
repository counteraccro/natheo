<?php

declare(strict_types=1);
/**
 * Identifiants des jetons CSRF des actions d'écriture sur les FAQ
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Faq;

enum FaqCsrfToken: string
{
    /**
     * Suppression d'une FAQ
     * @var string
     */
    case DELETE = 'faq_delete';

    /**
     * Activation / désactivation d'une FAQ
     * @var string
     */
    case DISABLED = 'faq_disabled';

    /**
     * Sauvegarde d'une FAQ
     * @var string
     */
    case SAVE = 'faq_save';

    /**
     * Création d'une FAQ
     * @var string
     */
    case NEW = 'faq_new';
}
