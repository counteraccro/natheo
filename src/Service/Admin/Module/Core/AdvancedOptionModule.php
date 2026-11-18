<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des options avancées pour le développement
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class AdvancedOptionModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::ADVANCED_OPTION->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_SUPER_ADMIN',
            label: 'admin_module.module.advanced_option.label',
            description: 'admin_module.module.advanced_option.description',
            routePrefixes: ['admin_advanced_options_'],
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
                label: 'admin_module.menu.advanced_option.label',
                icon: 'm8 8-4 4 4 4m8 0 4-4-4-4m-2-3-4 14',
                parent: CoreMenu::TOOLS,
                position: 10,
                legacyRoute: 'admin_advanced_options_index',
            ),
        ];
    }
}
