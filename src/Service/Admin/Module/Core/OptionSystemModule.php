<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des options système
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class OptionSystemModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::OPTION_SYSTEM->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_ADMIN',
            label: 'admin_module.module.option_system.label',
            description: 'admin_module.module.option_system.description',
            locked: true,
            routePrefixes: ['admin_option-system_'],
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
                label: 'admin_module.menu.option_system.label',
                icon: 'M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm16 14a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2ZM4 13a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6Zm16-2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6Z',
                parent: CoreMenu::SYSTEM,
                position: 20,
                legacyRoute: 'admin_option-system_change',
            ),
        ];
    }
}
