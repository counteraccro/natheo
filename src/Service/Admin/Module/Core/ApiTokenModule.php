<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Module des tokens de l'API, listés par type
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Global\SvgIcon;
use App\Enum\Admin\Module\CoreModuleId;
use App\Enum\Admin\System\ApiToken\ApiTokenScope;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

use function Symfony\Component\Translation\t;

#[AsAdminModule]
final class ApiTokenModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::API_TOKEN->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_ADMIN',
            label: t('admin_module.module.api_token.label', domain: 'admin_module'),
            description: t('admin_module.module.api_token.description', domain: 'admin_module'),
            routePrefixes: ['admin_api_token_'],
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
                label: t('admin_module.menu.api_token.label', domain: 'admin_module'),
                icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
                parent: CoreMenu::SYSTEM,
                position: 30,
            ),
            new AdminMenuItem(
                id: 'api_token_public',
                label: t('admin_module.menu.api_token_public.label', domain: 'admin_module'),
                icon: 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-9c-2.5 3-2.5 15 0 18m0-18c2.5 3 2.5 15 0 18M3.5 9h17m-17 6h17',
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_api_token_index',
                routeParameters: ['scope' => ApiTokenScope::PUBLIC->value],
            ),
            new AdminMenuItem(
                id: 'api_token_back_office',
                label: t('admin_module.menu.api_token_back_office.label', domain: 'admin_module'),
                icon: 'M9 16H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1h-4m-6 0v3m0-3h6m0 0v3M7 19h10',
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_api_token_index',
                routeParameters: ['scope' => ApiTokenScope::BACK_OFFICE->value],
            ),
            new AdminMenuItem(
                id: 'api_token_new',
                label: t('admin_module.menu.api_token_new.label', domain: 'admin_module'),
                icon: SvgIcon::PLUS->value,
                parent: self::ID,
                position: 30,
                legacyRoute: 'admin_api_token_add',
            ),
        ];
    }
}
