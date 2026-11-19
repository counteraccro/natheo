<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Module du tableau de bord, ouvert à tout utilisateur du back-office
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

use function Symfony\Component\Translation\t;

#[AsAdminModule]
final class DashboardModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::DASHBOARD->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_USER',
            label: t('admin_module.module.dashboard.label', domain: 'admin_module'),
            description: t('admin_module.module.dashboard.description', domain: 'admin_module'),
            locked: true,
            routePrefixes: ['admin_dashboard_'],
            routeExceptions: ['admin_dashboard_page_demo'],
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
                label: t('admin_module.menu.dashboard.label', domain: 'admin_module'),
                icon: 'm4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5',
                position: 10,
                legacyRoute: 'admin_dashboard_index',
            ),
        ];
    }
}
