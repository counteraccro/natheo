<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit un module du back-office en ressource AdminModule de l'API v2
 */

namespace App\Api\V2\Mapper\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminModule;
use App\Service\Admin\Module\AdminModule as AdminModuleDefinition;

final readonly class AdminModuleMapper
{
    /**
     * @param AdminModuleDefinition $definition
     * @param string $label nom traduit
     * @param string $description description traduite
     * @param string[] $operations noms des opérations d'API du module
     * @return AdminModule
     */
    public function map(
        AdminModuleDefinition $definition,
        string $label,
        string $description,
        array $operations,
    ): AdminModule {
        $module = new AdminModule();
        $module->id = $definition->id;
        $module->label = $label;
        $module->description = $description;
        $module->locked = $definition->locked;
        $module->operations = $operations;
        return $module;
    }
}
