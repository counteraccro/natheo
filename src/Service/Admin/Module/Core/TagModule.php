<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Module des tags des pages
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
final class TagModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::TAG->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: t('admin_module.module.tag.label', domain: 'admin_module'),
            description: t('admin_module.module.tag.description', domain: 'admin_module'),
            routePrefixes: ['admin_tag_'],
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
                label: t('admin_module.menu.tag.label', domain: 'admin_module'),
                icon: 'M15.2 6H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11.2a1 1 0 0 0 .747-.334l4.46-5a1 1 0 0 0 0-1.332l-4.46-5A1 1 0 0 0 15.2 6Z',
                parent: CoreMenu::CONTENT,
                position: 40,
            ),
            new AdminMenuItem(
                id: 'tag_index',
                label: t('admin_module.menu.tag_index.label', domain: 'admin_module'),
                icon: SvgIcon::LIST->value,
                parent: self::ID,
                position: 10,
                route: 'tag_index',
                path: 'tags',
            ),
            new AdminMenuItem(
                id: 'tag_new',
                label: t('admin_module.menu.tag_new.label', domain: 'admin_module'),
                icon: SvgIcon::PLUS->value,
                parent: self::ID,
                position: 20,
                route: 'tag_new',
                path: 'tags/new',
                shortcut: true,
            ),
        ];
    }
}
