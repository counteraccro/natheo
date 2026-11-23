<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Active ou désactive une opération d'API d'un module du back-office
 */

namespace App\Api\V2\State\Processor\Admin\System;

use ApiPlatform\Metadata\Operation;
use App\Api\V2\Input\Admin\System\UpdateActivationInput;
use App\Api\V2\Resource\Admin\System\AdminOperationActivation;
use App\Api\V2\State\Processor\Admin\AbstractAdminProcessor;
use App\Api\V2\State\Provider\Admin\System\AdminOperationActivationProvider;
use App\Enum\Admin\System\ActionLog\ActionLogAction;
use App\Service\Api\Admin\System\ApiAdminModuleActivationService;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @extends AbstractAdminProcessor<UpdateActivationInput, AdminOperationActivation>
 */
final class UpdateAdminOperationActivationProcessor extends AbstractAdminProcessor
{
    public function __construct(
        private readonly ApiAdminModuleActivationService $activationService,
        private readonly AdminOperationActivationProvider $provider,
    ) {}

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminOperationActivation
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    protected function write(
        mixed $data,
        Operation $operation,
        array $uriVariables,
        array $context,
    ): AdminOperationActivation {
        $this->activationService->setOperationDisabled((string) $uriVariables['id'], (bool) $data->disabled);
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
     * Nom de l'opération, son identifiant
     * @param mixed $resource
     * @return string|null
     */
    protected function getLogLabel(mixed $resource): ?string
    {
        return $resource instanceof AdminOperationActivation ? $resource->path : null;
    }
}
