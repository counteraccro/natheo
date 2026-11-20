<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Module de l'exemple de surcharge : un écran de assets/overwrite/ qui lit GET /api/v2/info, dont le provider est
 * surchargé
 */

namespace App\Overwrite\Module;

use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;
use App\Service\Admin\Module\Core\CoreMenu;

use function Symfony\Component\Translation\t;

#[AsAdminModule]
final class DemoOverwriteModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = 'demo_overwrite';

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_SUPER_ADMIN',
            label: t('demo_overwrite.module.label', domain: 'demo_overwrite'),
            description: t('demo_overwrite.module.description', domain: 'demo_overwrite'),
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
                label: t('demo_overwrite.menu.label', domain: 'demo_overwrite'),
                icon: 'M15.041 13.862A4.999 4.999 0 0 1 17 17.831V21M7 3v3.169a5 5 0 0 0 1.891 3.916M17 3v3.169a5 5 0 0 1-2.428 4.288l-5.144 3.086A5 5 0 0 0 7 17.831V21M7 5h10M7.399 8h9.252M8 16h8.652M7 19h10',
                parent: CoreMenu::TOOLS,
                position: 40,
                route: 'demo_overwrite',
                path: 'demo-overwrite',
            ),
        ];
    }
}
