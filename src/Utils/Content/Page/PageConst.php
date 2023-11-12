<?php
/**
 * Constantes pour les pages
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Page;

class PageConst
{
    /**
     * Affichage du rendu mode 1 block
     * @var integer
     */
    const RENDER_1_BLOCK = 1;

    /**
     * Affichage du rendu mode 2 block côte à côte
     * @var integer
     */
    const RENDER_2_BLOCK = 2;

    /**
     * Affichage du rendu mode 3 block côte à côte
     * @var integer
     */
    const RENDER_3_BLOCK = 3;

    /**
     * Affichage du rendu mode 2 bloc l'un en dessous de l'autre
     * @var integer
     */
    const RENDER_2_BLOCK_BOTTOM = 4;

    /**
     * Affichage du rendu mode 3 bloc l'un en dessous de l'autre
     * @var integer
     */
    const RENDER_3_BLOCK_BOTTOM = 5;

    /**
     * Affichage du rendu mode 1 block au dessus + 2 block côte à côte dessous
     * @var integer
     */
    const RENDER_1_2_BLOCK = 6;

    /**
     * Affichage du rendu mode 2 block côte à côte + 1 block en dessous
     * @var integer
     */
    const RENDER_2_1_BLOCK = 7;

    /**
     * Affichage du rendu mode 2 block côte à côte + 2 block côte à côte en dessous
     * @var integer
     */
    const RENDER_2_2_BLOCK = 8;

    /**
     * Status publié pour un article
     * @var integer
     */
    const STATUS_PUBLISH = 1;

    /**
     * Status brouillon pour un article
     * @var integer
     */
    const STATUS_DRAFT = 2;

    /**
     * Type texte pour les pages contents
     */
    const CONTENT_TYPE_TEXT = 1;




}
