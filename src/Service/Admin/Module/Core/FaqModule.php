<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Module des FAQ
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
final class FaqModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::FAQ->value;

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: t('admin_module.module.faq.label', domain: 'admin_module'),
            description: t('admin_module.module.faq.description', domain: 'admin_module'),
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
                label: t('admin_module.menu.faq.label', domain: 'admin_module'),
                icon: 'M9.529 9.988a2.502 2.502 0 1 1 5 .191A2.441 2.441 0 0 1 12 12.582V14m-.01 3.008H12M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                parent: CoreMenu::CONTENT,
                position: 60,
            ),
            new AdminMenuItem(
                id: 'faq_index',
                label: t('admin_module.menu.faq_index.label', domain: 'admin_module'),
                icon: SvgIcon::LIST->value,
                parent: self::ID,
                position: 10,
                route: 'faq_index',
                path: 'faqs',
                operation: 'admin_faq_get_collection',
            ),
            new AdminMenuItem(
                id: 'faq_new',
                label: t('admin_module.menu.faq_new.label', domain: 'admin_module'),
                icon: SvgIcon::PLUS->value,
                parent: self::ID,
                position: 20,
                route: 'faq_new',
                path: 'faqs/new',
                shortcut: true,
                operation: 'admin_faq_post',
            ),
        ];
    }
}
