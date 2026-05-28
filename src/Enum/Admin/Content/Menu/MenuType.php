<?php
/**
 * Enum sur les types de menu
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Menu;

enum MenuType: int
{
    /**
     * Menu header de type side bar
     * @var integer
     */
    case HEADER_SIDE_BAR = 1;

    /**
     * Menu header de type menu déroulant
     * @var integer
     */
    case HEADER_MENU_DEROULANT = 2;

    /**
     * Menu header de type menu déroulant, big menu
     * @var integer
     */
    case HEADER_MENU_DEROULANT_BIG_MENU = 3;

    /**
     * Menu header de type menu déroulant, big menu 2 colonnes
     * @var integer
     */
    case HEADER_MENU_DEROULANT_BIG_MENU_2_COLONNES = 4;

    /**
     * Menu header de type menu déroulant, big menu 3 colonnes
     * @var integer
     */
    case HEADER_MENU_DEROULANT_BIG_MENU_3_COLONNES = 5;

    /**
     * Menu header de type menu déroulant, big menu 4 colonnes
     * @var integer
     */
    case HEADER_MENU_DEROULANT_BIG_MENU_4_COLONNES = 6;

    /**
     * Menu gauche - droite side-bar
     * @var integer
     */
    case LEFT_RIGHT_SIDE_BAR = 11;

    /**
     * Menu gauche - droite side-bar accordéon
     * @var integer
     */
    case LEFT_RIGHT_SIDE_BAR_ACCORDEON = 12;

    /**
     * Menu footer 4 colonnes
     * @var integer
     */
    case FOOTER_COLONNES = 16;

    /**
     * Menu footer 1 ligne à droite
     * @var integer
     */
    case FOOTER_1_ROW_RIGHT = 17;

    /**
     * Menu footer 1 ligne centrée
     * @var integer
     */
    case FOOTER_1_ROW_CENTER = 18;
}
