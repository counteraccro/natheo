<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module du back-office chargé uniquement en env test, auquel est rattachée la ressource AdminProbe
 */

namespace App\Tests\Api\V2\Module;

use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class ProbeModule implements AdminModuleInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = 'probe';

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(id: self::ID, role: 'ROLE_CONTRIBUTEUR');
    }
}
