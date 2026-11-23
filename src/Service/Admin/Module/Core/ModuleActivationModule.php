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
        return [];
    }
}
