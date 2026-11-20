<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Module du back-office, déclaré avec l'attribut AsAdminModule. Ses entrées de menu reçoivent son rôle
 */

namespace App\Service\Admin\Module;

interface AdminModuleInterface extends AdminMenuProviderInterface
{
    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule;
}
