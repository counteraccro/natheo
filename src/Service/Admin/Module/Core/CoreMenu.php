<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Dossiers du menu du back-office qui n'appartiennent à aucun module
 */

namespace App\Service\Admin\Module\Core;

use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminMenuProviderInterface;
use App\Service\Admin\Module\AsAdminMenu;

use function Symfony\Component\Translation\t;

#[AsAdminMenu]
final class CoreMenu implements AdminMenuProviderInterface
{
    /**
     * Dossier des modules de contenu
     * @var string
     */
    public const string CONTENT = 'content';

    /**
     * Dossier des modules système
     * @var string
     */
    public const string SYSTEM = 'system';

    /**
     * Dossier des outils de développement
     * @var string
     */
    public const string TOOLS = 'tools';

    /**
     * @return AdminMenuItem[]
     */
    public function getMenuItems(): array
    {
        return [
            new AdminMenuItem(
                id: self::CONTENT,
                label: t('admin_module.menu.content.label', domain: 'admin_module'),
                icon: 'M10 3v4a1 1 0 0 1-1 1H5m4 8h6m-6-4h6m4-8v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z',
                position: 20,
            ),
            new AdminMenuItem(
                id: self::SYSTEM,
                label: t('admin_module.menu.system.label', domain: 'admin_module'),
                icon: 'M8 8v8m0-8h8M8 8H6a2 2 0 1 1 2-2v2Zm0 8h8m-8 0H6a2 2 0 1 0 2 2v-2Zm8 0V8m0 8h2a2 2 0 1 1-2 2v-2Zm0-8h2a2 2 0 1 0-2-2v2Z',
                position: 30,
            ),
            new AdminMenuItem(
                id: self::TOOLS,
                label: t('admin_module.menu.tools.label', domain: 'admin_module'),
                icon: 'm8 8-4 4 4 4m8 0 4-4-4-4m-2-3-4 14',
                position: 40,
            ),
        ];
    }
}
