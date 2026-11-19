<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Module des utilisateurs du back-office. Les pages personnelles (compte, options, mot de passe) restent ouvertes à tous
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Global\SvgIcon;
use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

use function Symfony\Component\Translation\t;

#[AsAdminModule]
final class UserModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::USER->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_SUPER_ADMIN',
            label: t('admin_module.module.user.label', domain: 'admin_module'),
            description: t('admin_module.module.user.description', domain: 'admin_module'),
            locked: true,
            routePrefixes: ['admin_user_'],
            routeExceptions: [
                'admin_user_my_option',
                'admin_user_ajax_update_my_option',
                'admin_user_my_account',
                'admin_user_delete_avatar',
                'admin_user_change_my_password',
                'admin_user_self_disabled',
                'admin_user_self_delete',
                'admin_user_update_user_data',
            ],
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
                label: t('admin_module.menu.user.label', domain: 'admin_module'),
                icon: 'M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                parent: CoreMenu::SYSTEM,
                position: 10,
            ),
            new AdminMenuItem(
                id: 'user_index',
                label: t('admin_module.menu.user_index.label', domain: 'admin_module'),
                icon: SvgIcon::LIST->value,
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_user_index',
            ),
            new AdminMenuItem(
                id: 'user_new',
                label: t('admin_module.menu.user_new.label', domain: 'admin_module'),
                icon: SvgIcon::PLUS->value,
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_user_add',
            ),
        ];
    }
}
