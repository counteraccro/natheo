<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Module des options avancées pour le développement
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

use function Symfony\Component\Translation\t;

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
            label: t('admin_module.module.advanced_option.label', domain: 'admin_module'),
            description: t('admin_module.module.advanced_option.description', domain: 'admin_module'),
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
                label: t('admin_module.menu.advanced_option.label', domain: 'admin_module'),
                icon: 'm8 8-4 4 4 4m8 0 4-4-4-4m-2-3-4 14',
                parent: CoreMenu::TOOLS,
                position: 10,
                legacyRoute: 'admin_advanced_options_index',
            ),
        ];
    }
}
