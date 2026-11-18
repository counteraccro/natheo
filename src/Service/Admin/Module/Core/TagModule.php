<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des tags des pages
 */

namespace App\Service\Admin\Module\Core;

use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class TagModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = 'tag';

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: 'admin_module.module.tag.label',
            description: 'admin_module.module.tag.description',
            locked: true,
            routePrefixes: ['admin_tag_'],
        );
    }

    /**
     * @return AdminMenuItem[]
     */
    public function getMenuItems(): array
    {
        return [
            new AdminMenuItem(
                id: self::ID,
                label: 'admin_module.menu.tag.label',
                icon: 'M15.2 6H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11.2a1 1 0 0 0 .747-.334l4.46-5a1 1 0 0 0 0-1.332l-4.46-5A1 1 0 0 0 15.2 6Z',
                parent: CoreMenu::CONTENT,
                position: 40,
            ),
            new AdminMenuItem(
                id: 'tag_index',
                label: 'admin_module.menu.tag_index.label',
                icon: 'M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5',
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_tag_index',
            ),
            new AdminMenuItem(
                id: 'tag_new',
                label: 'admin_module.menu.tag_new.label',
                icon: 'M5 12h14m-7 7V5',
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_tag_add',
                shortcut: true,
            ),
        ];
    }
}
