<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Module des pages du site public
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
final class PageModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::PAGE->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: t('admin_module.module.page.label', domain: 'admin_module'),
            description: t('admin_module.module.page.description', domain: 'admin_module'),
            routePrefixes: ['admin_page_'],
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
                label: t('admin_module.menu.page.label', domain: 'admin_module'),
                icon: 'M10 3v4a1 1 0 0 1-1 1H5m14-4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 18h8l-2-4-1.5 2-2-4L8 18Zm7-8.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0Z',
                parent: CoreMenu::CONTENT,
                position: 20,
            ),
            new AdminMenuItem(
                id: 'page_index',
                label: t('admin_module.menu.page_index.label', domain: 'admin_module'),
                icon: SvgIcon::LIST->value,
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_page_index',
            ),
            new AdminMenuItem(
                id: 'page_new',
                label: t('admin_module.menu.page_new.label', domain: 'admin_module'),
                icon: SvgIcon::PLUS->value,
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_page_add',
                shortcut: true,
            ),
        ];
    }
}
