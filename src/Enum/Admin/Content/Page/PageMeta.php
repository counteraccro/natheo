<?php
/**
 * Enum sur les meta tags par défaut de la page
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageMeta: string
{
    case DESCRIPTION = 'description';
    case KEYWORDS = 'keywords';
    case AUTHOR = 'author';
    case COPYRIGHT = 'copyright';
}
