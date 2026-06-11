<?php
/**
 * Status des pages
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageStatus: int
{
    /**
     * Status publié pour une page
     */
    case PUBLISH = 1;

    /**
     * Status draft pour une page
     */
    case DRAFT = 2;
}
