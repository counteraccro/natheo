<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Active ou désactive un module du back-office
 */

namespace App\Api\V2\State\Processor\Admin\System;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\System\UpdateActivationInput;
use App\Api\V2\Resource\Admin\System\AdminModuleActivation;
use App\Api\V2\State\Processor\Admin\AbstractAdminProcessor;
use App\Api\V2\State\Provider\Admin\System\AdminModuleActivationProvider;
use App\Enum\Admin\System\ActionLog\ActionLogAction;
use App\Service\Api\Admin\System\ApiAdminModuleActivationService;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @extends AbstractAdminProcessor<UpdateActivationInput, AdminModuleActivation>
 */
final class UpdateAdminModuleActivationProcessor extends AbstractAdminProcessor
{
    public function __construct(
        private readonly ApiAdminModuleActivationService $activationService,
        private readonly AdminModuleActivationProvider $provider,
    ) {}

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminModuleActivation
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    protected function write(
        mixed $data,
        Operation $operation,
        array $uriVariables,
        array $context,
    ): AdminModuleActivation {
        $this->activationService->setModuleDisabled((string) $uriVariables['id'], (bool) $data->disabled);
        return $this->provider->provide($operation, $uriVariables, $context);
    }

    /**
     * @param Operation $operation
     * @param mixed $data
     * @return string
     */
    protected function getLogAction(Operation $operation, mixed $data): string
    {
        return ($data->disabled ? ActionLogAction::DISABLE : ActionLogAction::ENABLE)->value;
    }

    /**
     * @param mixed $resource
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        return $resource instanceof AdminModuleActivation ? $resource->label : null;
    }
}
