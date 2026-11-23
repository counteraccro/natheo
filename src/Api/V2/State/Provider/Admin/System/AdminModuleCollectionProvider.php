<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Provider de la liste des modules du back-office visibles par l'utilisateur de la session, avec leurs opérations activées
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Mapper\Admin\System\AdminModuleMapper;
use App\Api\V2\Metadata\AdminModuleOperationFinder;
use App\Api\V2\Resource\Admin\System\AdminModule;
use App\Service\Admin\Module\AdminMenuBuilder;
use App\Service\Admin\Module\AdminModule as AdminModuleDefinition;
use App\Service\Admin\Module\ModuleActivationService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminModule>
 */
final readonly class AdminModuleCollectionProvider implements ProviderInterface
{
    public function __construct(
        private AdminMenuBuilder $menuBuilder,
        private AdminModuleOperationFinder $operationFinder,
        private TranslatorInterface $translator,
        private ModuleActivationService $activationService,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminModule[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $mapper = new AdminModuleMapper();
        return array_map(
            fn(AdminModuleDefinition $definition) => $mapper->map(
                $definition,
                $definition->label->trans($this->translator),
                $definition->description->trans($this->translator),
                array_values(
                    array_filter(
                        $this->operationFinder->find($definition->id),
                        fn(string $name) => $this->activationService->isOperationEnabled($name),
                    ),
                ),
            ),
            $this->menuBuilder->getVisibleModules(),
        );
    }
}
