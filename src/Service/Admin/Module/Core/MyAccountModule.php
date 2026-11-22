<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des pages personnelles de chaque utilisateur (compte, options, mot de passe), sans entrée de menu
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

use function Symfony\Component\Translation\t;

#[AsAdminModule]
final class MyAccountModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::MY_ACCOUNT->value;

    /**
     * Routes du module, exclues du module des utilisateurs
     * @var string[]
     */
    public const array ROUTES = [
        'admin_user_my_option',
        'admin_user_ajax_update_my_option',
        'admin_user_my_account',
        'admin_user_delete_avatar',
        'admin_user_change_my_password',
        'admin_user_self_disabled',
        'admin_user_self_delete',
        'admin_user_update_user_data',
    ];

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_USER',
            label: t('admin_module.module.my_account.label', domain: 'admin_module'),
            description: t('admin_module.module.my_account.description', domain: 'admin_module'),
            locked: true,
            routePrefixes: self::ROUTES,
        );
    }

    /**
     * @return AdminMenuItem[]
     */
    public function getMenuItems(): array
    {
        return [];
    }
}
