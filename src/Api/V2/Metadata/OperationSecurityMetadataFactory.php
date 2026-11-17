<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Sécurité des opérations selon le type du token : publiques, partagées par les deux types, ou d'administration
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Enum\Admin\System\ApiToken\ApiTokenScope;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: 'api_platform.metadata.resource.metadata_collection_factory', priority: 0)]
final readonly class OperationSecurityMetadataFactory implements ResourceMetadataCollectionFactoryInterface
{
    /**
     * Clé d'extraProperties d'une opération ouverte aux tokens publics et back-office (sessions, infos)
     * @var string
     */
    public const string SHARED = 'shared';

    public function __construct(#[AutowireDecorated] private ResourceMetadataCollectionFactoryInterface $decorated) {}

    /**
     * @param string $resourceClass
     * @return ResourceMetadataCollection
     */
    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $collection = $this->decorated->create($resourceClass);

        foreach ($collection as $index => $resource) {
            $operations = $resource->getOperations();
            if ($resource instanceof ErrorResource || $operations === null) {
                continue;
            }
            foreach ($operations as $name => $operation) {
                $operations->add($name, $operation->withSecurity($this->buildSecurity($operation)));
            }
            $collection[$index] = $resource->withOperations($operations);
        }
        return $collection;
    }

    /**
     * Expression de sécurité de l'opération, croisée avec le type du token
     * @param Operation $operation
     * @return string|null
     */
    private function buildSecurity(Operation $operation): ?string
    {
        $security = $operation->getSecurity();
        if (($operation->getExtraProperties()[self::SHARED] ?? false) === true) {
            return $security;
        }
        return $this->combine(sprintf("is_granted('%s')", ApiTokenScope::PUBLIC->getRole()), $security);
    }

    /**
     * @param string $condition
     * @param string|null $security
     * @return string
     */
    private function combine(string $condition, ?string $security): string
    {
        return $security === null || $security === '' ? $condition : sprintf('%s and (%s)', $condition, $security);
    }
}
