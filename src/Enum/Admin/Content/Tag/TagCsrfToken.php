<?php

declare(strict_types=1);
/**
 * Identifiants des jetons CSRF des actions d'écriture sur les tags
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Tag;

enum TagCsrfToken: string
{
    /**
     * Ajout ou création d'un tag depuis l'éditeur de page
     * @var string
     */
    case TAG_BY_NAME = 'tag_by_name';
}
