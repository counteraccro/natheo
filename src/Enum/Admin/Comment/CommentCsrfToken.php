<?php

declare(strict_types=1);
/**
 * Identifiants des jetons CSRF des actions d'écriture sur les commentaires
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Comment;

enum CommentCsrfToken: string
{
    /**
     * Sauvegarde d'un commentaire
     * @var string
     */
    case SAVE = 'comment_save';

    /**
     * Modération d'une liste de commentaires
     * @var string
     */
    case MODERATE = 'comment_moderate';
}
