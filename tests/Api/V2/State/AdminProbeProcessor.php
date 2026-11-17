<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Processor des écritures de la ressource de test AdminProbe
 */

namespace App\Tests\Api\V2\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use App\Api\V2\State\Processor\Admin\AbstractAdminProcessor;
use App\Tests\Api\V2\Resource\Admin\AdminProbe;

/**
 * @extends AbstractAdminProcessor<AdminProbe, AdminProbe|null>
 */
final class AdminProbeProcessor extends AbstractAdminProcessor
{
    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminProbe|null
     */
    protected function write(mixed $data, Operation $operation, array $uriVariables, array $context): ?AdminProbe
    {
        if ($operation instanceof Delete) {
            return null;
        }
        $data->id ??= 4;
        $data->updatedAt = AdminProbe::UPDATED_AT_AFTER_WRITE;
        return $data;
    }

    /**
     * @param mixed $resource
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        return $resource instanceof AdminProbe ? $resource->name : null;
    }
}
