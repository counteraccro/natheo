<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des menus du site public
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Global\SvgIcon;
use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class MenuModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::MENU->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: 'admin_module.module.menu.label',
            description: 'admin_module.module.menu.description',
            locked: true,
            routePrefixes: ['admin_menu_'],
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
                label: 'admin_module.menu.menu.label',
                icon: 'M5 7h14M5 12h14M5 17h14',
                parent: CoreMenu::CONTENT,
                position: 10,
            ),
            new AdminMenuItem(
                id: 'menu_index',
                label: 'admin_module.menu.menu_index.label',
                icon: SvgIcon::LIST->value,
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_menu_index',
            ),
            new AdminMenuItem(
                id: 'menu_new',
                label: 'admin_module.menu.menu_new.label',
                icon: SvgIcon::PLUS->value,
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_menu_add',
                shortcut: true,
            ),
        ];
    }
}
