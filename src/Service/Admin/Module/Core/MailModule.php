<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des emails envoyés par le CMS
 */

namespace App\Service\Admin\Module\Core;

use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class MailModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = 'mail';

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_ADMIN',
            label: 'admin_module.module.mail.label',
            description: 'admin_module.module.mail.description',
            routePrefixes: ['admin_mail_'],
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
                label: 'admin_module.menu.mail.label',
                icon: 'm3.5 5.5 7.893 6.036a1 1 0 0 0 1.214 0L20.5 5.5M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z',
                parent: CoreMenu::SYSTEM,
                position: 50,
                legacyRoute: 'admin_mail_index',
            ),
        ];
    }
}
