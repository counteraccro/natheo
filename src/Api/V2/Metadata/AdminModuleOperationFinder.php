<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Noms des opérations d'API rattachées à chaque module du back-office
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;

final class AdminModuleOperationFinder
{
    /**
     * Noms des opérations indexés par identifiant de module, construits au premier appel
     * @var array<string, string[]>|null
     */
    private ?array $operations = null;

    public function __construct(
        private readonly ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {}

    /**
     * Noms des opérations du module, triés
     * @param string $module
     * @return string[]
     */
    public function find(string $module): array
    {
        if ($this->operations === null) {
            $this->operations = $this->build($this->resourceNameCollectionFactory->create());
        }
        return $this->operations[$module] ?? [];
    }

    /**
     * @param ResourceNameCollection $resourceClasses
     * @return array<string, string[]>
     */
    private function build(ResourceNameCollection $resourceClasses): array
    {
        $operations = [];
        foreach ($resourceClasses as $resourceClass) {
            foreach ($this->resourceMetadataCollectionFactory->create($resourceClass) as $resource) {
                foreach ($resource->getOperations() ?? [] as $operation) {
                    $module = $operation->getExtraProperties()[OperationSecurityMetadataFactory::MODULE] ?? null;
                    if (is_string($module) && $operation->getName() !== null) {
                        $operations[$module][] = $operation->getName();
                    }
                }
            }
        }
        foreach ($operations as &$names) {
            $names = array_values(array_unique($names));
            sort($names);
        }
        return $operations;
    }
}
