<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider d'un module du back-office visible par l'utilisateur de la session
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Mapper\Admin\System\AdminModuleMapper;
use App\Api\V2\Metadata\AdminModuleOperationFinder;
use App\Api\V2\Resource\Admin\System\AdminModule;
use App\Service\Admin\Module\AdminMenuBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminModule>
 */
final readonly class AdminModuleProvider implements ProviderInterface
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
     * @return AdminModule
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminModule
    {
        $id = (string) ($uriVariables['id'] ?? '');
        foreach ($this->menuBuilder->getVisibleModules() as $definition) {
            if ($definition->id === $id) {
                return (new AdminModuleMapper())->map(
                    $definition,
                    $this->translator->trans($definition->label, domain: $definition->translationDomain),
                    $this->translator->trans($definition->description, domain: $definition->translationDomain),
                    $this->operationFinder->find($definition->id),
                );
            }
        }
        throw new NotFoundException(
            $this->translator->trans('api_errors.find.admin_module.not_found', domain: 'api_errors'),
        );
    }
}
