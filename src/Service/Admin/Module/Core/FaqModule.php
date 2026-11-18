<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des FAQ
 */

namespace App\Service\Admin\Module\Core;

use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class FaqModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = 'faq';

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: 'admin_module.module.faq.label',
            description: 'admin_module.module.faq.description',
            routePrefixes: ['admin_faq_'],
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
                label: 'admin_module.menu.faq.label',
                icon: 'M9.529 9.988a2.502 2.502 0 1 1 5 .191A2.441 2.441 0 0 1 12 12.582V14m-.01 3.008H12M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                parent: CoreMenu::CONTENT,
                position: 60,
            ),
            new AdminMenuItem(
                id: 'faq_index',
                label: 'admin_module.menu.faq_index.label',
                icon: 'M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5',
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_faq_index',
            ),
            new AdminMenuItem(
                id: 'faq_new',
                label: 'admin_module.menu.faq_new.label',
                icon: 'M5 12h14m-7 7V5',
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_faq_add',
                shortcut: true,
            ),
        ];
    }
}
