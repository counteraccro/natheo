<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la liste des modules du back-office visibles par l'utilisateur de la session
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Mapper\Admin\System\AdminModuleMapper;
use App\Api\V2\Metadata\AdminModuleOperationFinder;
use App\Api\V2\Resource\Admin\System\AdminModule;
use App\Service\Admin\Module\AdminMenuBuilder;
use App\Service\Admin\Module\AdminModule as AdminModuleDefinition;
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
                $this->translator->trans($definition->label, domain: $definition->translationDomain),
                $this->translator->trans($definition->description, domain: $definition->translationDomain),
                $this->operationFinder->find($definition->id),
            ),
            $this->menuBuilder->getVisibleModules(),
        );
    }
}
