<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des pages du site public
 */

namespace App\Service\Admin\Module\Core;

use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class PageModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = 'page';

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: 'admin_module.module.page.label',
            description: 'admin_module.module.page.description',
            locked: true,
            routePrefixes: ['admin_page_'],
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
                label: 'admin_module.menu.page.label',
                icon: 'M10 3v4a1 1 0 0 1-1 1H5m14-4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 18h8l-2-4-1.5 2-2-4L8 18Zm7-8.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0Z',
                parent: CoreMenu::CONTENT,
                position: 20,
            ),
            new AdminMenuItem(
                id: 'page_index',
                label: 'admin_module.menu.page_index.label',
                icon: 'M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5',
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_page_index',
            ),
            new AdminMenuItem(
                id: 'page_new',
                label: 'admin_module.menu.page_new.label',
                icon: 'M5 12h14m-7 7V5',
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_page_add',
                shortcut: true,
            ),
        ];
    }
}
