<?php

declare(strict_types=1);
/**
 * Enum type de render des pages
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageRender: int
{
    /**
     *   Affichage du rendu mode 1 block
     */
    case ONE_BLOCK = 1;

    /**
     * Affichage du rendu mode 2 block côte à côte
     */
    case TWO_BLOCK = 2;

    /**
     * Affichage du rendu mode 3 block côte à côte
     */
    case THREE_BLOCK = 3;

    /**
     * Affichage du rendu mode 2 bloc l'un en dessous de l'autre
     */
    case TWO_BLOCK_BOTTOM = 4;

    /**
     * Affichage du rendu mode 3 bloc l'un en dessous de l'autre
     */
    case THREE_BLOCK_BOTTOM = 5;

    /**
     * Affichage du rendu mode 1 block au dessus + 2 block côte à côte dessous
     */
    case ONE_TWO_BLOCK = 6;

    /**
     * Affichage du rendu mode 2 block côte à côte + 1 block en dessous
     */
    case TWO_ONE_BLOCK = 7;

    /**
     * Affichage du rendu mode 2 block côte à côte + 2 block côte à côte en dessous
     */
    case TWO_TWO_BLOCK = 8;
}
