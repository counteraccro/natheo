<?php
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageCategory: int
{
    /**
     * Catégorie page
     */
    case PAGE = 1;

    /**
     * Catégorie article
     */
    case ARTICLE = 2;

    /**
     * Catégorie projet
     */
    case PROJET = 3;

    /**
     * Catégorie blog
     */
    case BLOG = 4;

    /**
     * Catégorie évènement
     */
    case EVENEMENT = 5;

    /**
     * Catégorie news
     */
    case NEWS = 6;

    /**
     * Catégorie évolution
     */
    case EVOLUTION = 7;

    /**
     * Catégorie documentation
     */
    case DOCUMENTATION = 8;

    /**
     * Catégorie FAQ
     */
    case FAQ = 9;
}
