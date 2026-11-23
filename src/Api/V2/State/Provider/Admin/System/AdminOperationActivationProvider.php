<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de l'activation d'une opération d'API d'un module du back-office
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Resource\Admin\System\AdminOperationActivation;
use App\Service\Api\Admin\System\ApiAdminModuleActivationService;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminOperationActivation>
 */
final readonly class AdminOperationActivationProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminModuleActivationService $activationService,
        private AdminModuleActivationCollectionProvider $collectionProvider,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminOperationActivation
     * @throws NotFoundException si l'opération n'appartient à aucun module
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): AdminOperationActivation {
        $found =
            $this->activationService->findOperation((string) ($uriVariables['id'] ?? '')) ??
            throw new NotFoundException(
                $this->translator->trans('module_activation.error.operation_not_found', domain: 'module_activation'),
            );
        return $this->collectionProvider->mapOperation($found);
    }
}
