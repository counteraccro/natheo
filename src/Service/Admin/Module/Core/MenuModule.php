<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Module des menus du site public
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
            label: t('admin_module.module.menu.label', domain: 'admin_module'),
            description: t('admin_module.module.menu.description', domain: 'admin_module'),
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
                label: t('admin_module.menu.menu.label', domain: 'admin_module'),
                icon: 'M5 7h14M5 12h14M5 17h14',
                parent: CoreMenu::CONTENT,
                position: 10,
            ),
            new AdminMenuItem(
                id: 'menu_index',
                label: t('admin_module.menu.menu_index.label', domain: 'admin_module'),
                icon: SvgIcon::LIST->value,
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_menu_index',
            ),
            new AdminMenuItem(
                id: 'menu_new',
                label: t('admin_module.menu.menu_new.label', domain: 'admin_module'),
                icon: SvgIcon::PLUS->value,
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_menu_add',
                shortcut: true,
            ),
        ];
    }
}
