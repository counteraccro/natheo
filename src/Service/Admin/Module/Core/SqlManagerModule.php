<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module du gestionnaire de requêtes SQL
 */

namespace App\Service\Admin\Module\Core;

use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class SqlManagerModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = 'sql_manager';

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_SUPER_ADMIN',
            label: 'admin_module.module.sql_manager.label',
            description: 'admin_module.module.sql_manager.description',
            routePrefixes: ['admin_sql_manager_'],
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
                label: 'admin_module.menu.sql_manager.label',
                icon: 'm8 9 3 3-3 3m5 0h3M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z',
                parent: CoreMenu::TOOLS,
                position: 20,
            ),
            new AdminMenuItem(
                id: 'sql_manager_index',
                label: 'admin_module.menu.sql_manager_index.label',
                icon: 'M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5',
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_sql_manager_index',
            ),
            new AdminMenuItem(
                id: 'sql_manager_new',
                label: 'admin_module.menu.sql_manager_new.label',
                icon: 'M5 12h14m-7 7V5',
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_sql_manager_add',
            ),
        ];
    }
}
