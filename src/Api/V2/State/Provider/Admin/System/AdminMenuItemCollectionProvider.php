<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la liste des entrées du menu du back-office visibles par l'utilisateur de la session
 */

namespace App\Api\V2\State\Provider\Admin\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Mapper\Admin\System\AdminMenuItemMapper;
use App\Api\V2\Resource\Admin\System\AdminMenuItem;
use App\Service\Admin\Module\AdminMenuBuilder;
use App\Service\Admin\Module\AdminMenuNode;

/**
 * @implements ProviderInterface<AdminMenuItem>
 */
final readonly class AdminMenuItemCollectionProvider implements ProviderInterface
{
    public function __construct(private AdminMenuBuilder $menuBuilder) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminMenuItem[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $mapper = new AdminMenuItemMapper();
        return array_map(fn(AdminMenuNode $node) => $mapper->map($node), $this->menuBuilder->getListedNodes());
    }
}
