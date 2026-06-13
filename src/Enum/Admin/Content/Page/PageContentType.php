<?php
/**
 * Type de PageContent
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageContentType: int
{
    /**
     * Type texte pour les pages contents
     */
    case TEXT = 1;

    /**
     * Type FAQ pour les pages contents
     */
    case FAQ = 2;

    /**
     * Type listing pour les pages contents
     */
    case LISTING = 3;
}
