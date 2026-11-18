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
use App\Api\V2\Resource\Admin\System\AdminModule;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<AdminModule>
 */
final readonly class AdminModuleProvider implements ProviderInterface
{
    public function __construct(
        private AdminModuleCollectionProvider $collectionProvider,
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
        foreach ($this->collectionProvider->provide($operation, [], $context) as $module) {
            if ($module->id === $id) {
                return $module;
            }
        }
        throw new NotFoundException(
            $this->translator->trans('api_errors.find.admin_module.not_found', domain: 'api_errors'),
        );
    }
}
