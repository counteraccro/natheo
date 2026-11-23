<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de l'activation de tous les modules du back-office, dans l'ordre du menu
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Mapper\Admin\System\AdminModuleActivationMapper;
use App\Api\V2\Resource\Admin\System\AdminModuleActivation;
use App\Api\V2\Resource\Admin\System\AdminOperationActivation;
use App\Service\Admin\Module\AdminModule;
use App\Service\Api\Admin\System\ApiAdminModuleActivationService;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @implements ProviderInterface<AdminModuleActivation>
 */
final readonly class AdminModuleActivationCollectionProvider implements ProviderInterface
{
    public function __construct(private ApiAdminModuleActivationService $activationService) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminModuleActivation[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(fn(AdminModule $module) => $this->map($module), $this->activationService->getModules());
    }

    /**
     * @param AdminModule $module
     * @return AdminModuleActivation
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function map(AdminModule $module): AdminModuleActivation
    {
        $mapper = new AdminModuleActivationMapper();
        return $mapper->map(
            $module,
            $this->activationService->getModuleTexts($module),
            $this->activationService->isModuleLocked($module->id),
            $this->activationService->isModuleDisabled($module->id),
            array_map(
                fn(array $operation) => $this->mapOperation($operation),
                $this->activationService->getOperations($module->id),
            ),
        );
    }

    /**
     * @param array{name: string, module: string, method: string, path: string, locked: bool} $operation
     * @return AdminOperationActivation
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function mapOperation(array $operation): AdminOperationActivation
    {
        return (new AdminModuleActivationMapper())->mapOperation(
            $operation,
            $this->activationService->isOperationDisabled($operation),
        );
    }
}
