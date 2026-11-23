<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module de l'activation des modules du back-office et de leurs opérations d'API, toujours verrouillé
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

use function Symfony\Component\Translation\t;

#[AsAdminModule]
final class ModuleActivationModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::MODULE_ACTIVATION->value;

    /**
     * Icône de l'entrée de menu (grille)
     * @var string
     */
    private const string ICON = 'M4.857 3h4.286C9.616 3 10 3.384 10 3.857v4.286C10 8.616 9.616 9 9.143 9H4.857A.857.857 0 0 1 4 8.143V3.857C4 3.384 4.384 3 4.857 3Zm10 0h4.286c.473 0 .857.384.857.857v4.286c0 .473-.384.857-.857.857h-4.286A.857.857 0 0 1 14 8.143V3.857c0-.473.384-.857.857-.857Zm-10 10h4.286c.473 0 .857.384.857.857v4.286c0 .473-.384.857-.857.857H4.857A.857.857 0 0 1 4 19.143v-4.286c0-.473.384-.857.857-.857Zm10 0h4.286c.473 0 .857.384.857.857v4.286c0 .473-.384.857-.857.857h-4.286a.857.857 0 0 1-.857-.857v-4.286c0-.473.384-.857.857-.857Z';

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_SUPER_ADMIN',
            label: t('admin_module.module.module_activation.label', domain: 'admin_module'),
            description: t('admin_module.module.module_activation.description', domain: 'admin_module'),
            locked: true,
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
                label: t('admin_module.menu.module_activation.label', domain: 'admin_module'),
                icon: self::ICON,
                parent: CoreMenu::SYSTEM,
                position: 90,
                route: 'module_activation_index',
                path: 'modules',
            ),
        ];
    }
}
