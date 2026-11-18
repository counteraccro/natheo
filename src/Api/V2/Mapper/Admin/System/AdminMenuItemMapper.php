<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit une entrée du menu visible par l'utilisateur en ressource AdminMenuItem de l'API v2
 */

namespace App\Api\V2\Mapper\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminMenuItem;
use App\Service\Admin\Module\AdminMenuNode;

final readonly class AdminMenuItemMapper
{
    /**
     * @param AdminMenuNode $node
     * @return AdminMenuItem
     */
    public function map(AdminMenuNode $node): AdminMenuItem
    {
        $item = new AdminMenuItem();
        $item->id = $node->item->id;
        $item->parent = $node->item->parent;
        $item->module = $node->module;
        $item->label = $node->label;
        $item->icon = $node->item->icon;
        $item->position = $node->item->position;
        $item->legacyUrl = $node->url;
        $item->shortcut = $node->item->shortcut;
        $item->hidden = !$node->inMenu;
        $item->badge = $node->badge;
        return $item;
    }
}
