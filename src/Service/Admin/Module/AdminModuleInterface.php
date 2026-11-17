<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module du back-office, déclaré avec l'attribut AsAdminModule
 */

namespace App\Service\Admin\Module;

interface AdminModuleInterface
{
    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule;
}
