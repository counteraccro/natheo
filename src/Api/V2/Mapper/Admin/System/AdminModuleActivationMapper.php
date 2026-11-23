<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion d'un module du back-office et de ses opérations en ressources d'activation
 */

namespace App\Api\V2\Mapper\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminModuleActivation;
use App\Api\V2\Resource\Admin\System\AdminOperationActivation;
use App\Service\Admin\Module\AdminModule;

final readonly class AdminModuleActivationMapper
{
    /**
     * Suffixes des noms d'opération, du plus long au plus court
     * @var string[]
     */
    private const array KINDS = ['get_collection', 'get', 'post', 'put', 'patch', 'delete'];

    /**
     * @param AdminModule $definition
     * @param array{label: string, description: string} $texts libellé et description traduits
     * @param bool $locked
     * @param bool $disabled
     * @param AdminOperationActivation[] $operations
     * @return AdminModuleActivation
     */
    public function map(
        AdminModule $definition,
        array $texts,
        bool $locked,
        bool $disabled,
        array $operations,
    ): AdminModuleActivation {
        $module = new AdminModuleActivation();
        $module->id = $definition->id;
        $module->label = $texts['label'];
        $module->description = $texts['description'];
        $module->locked = $locked;
        $module->disabled = $disabled;
        $module->operations = $operations;
        return $module;
    }

    /**
     * @param array{name: string, module: string, method: string, path: string, locked: bool} $operation
     * @param bool $disabled
     * @return AdminOperationActivation
     */
    public function mapOperation(array $operation, bool $disabled): AdminOperationActivation
    {
        $resource = new AdminOperationActivation();
        $resource->id = $operation['name'];
        $resource->module = $operation['module'];
        $resource->method = $operation['method'];
        $resource->path = $operation['path'];
        $resource->kind = $this->getKind($operation['name'], $operation['method']);
        $resource->locked = $operation['locked'];
        $resource->disabled = $disabled;
        return $resource;
    }

    /**
     * Nature lue dans le suffixe du nom (admin_tag_get_collection), la méthode HTTP à défaut
     * @param string $name
     * @param string $method
     * @return string
     */
    private function getKind(string $name, string $method): string
    {
        foreach (self::KINDS as $kind) {
            if (str_ends_with($name, '_' . $kind)) {
                return $kind;
            }
        }
        return strtolower($method);
    }
}
