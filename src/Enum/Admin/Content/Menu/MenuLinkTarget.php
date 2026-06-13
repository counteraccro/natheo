<?php

declare(strict_types=1);
/**
 * Type de target pour les liens
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Menu;

enum MenuLinkTarget: string
{
    /**
     * Link target blank
     * @var string
     */
    case LINK_TARGET_BLANK = '_blank';

    /**
     * Link target self
     * @var string
     */
    case LINK_TARGET_SELF = '_self';
}
