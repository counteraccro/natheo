<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ajoute SqlParameter à chaque collection d'administration, réservé au super administrateur quand il est envoyé
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: 'api_platform.metadata.resource.metadata_collection_factory', priority: 1001)]
final readonly class AdminSqlParameterMetadataFactory implements ResourceMetadataCollectionFactoryInterface
{
    public function __construct(#[AutowireDecorated] private ResourceMetadataCollectionFactoryInterface $decorated) {}

    /**
     * Placé avant la fabrique des paramètres d'API Platform, qui complète SqlParameter comme les autres
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
                $isAdmin = str_starts_with(
                    (string) $operation->getUriTemplate(),
                    OperationSecurityMetadataFactory::ADMIN_PREFIX,
                );
                if (!($operation instanceof GetCollection) || !$isAdmin) {
                    continue;
                }
                $parameters = $operation->getParameters() ?? new Parameters();
                $parameters->add(SqlParameter::KEY, new SqlParameter());
                $condition = sprintf(
                    "(request.query.get('%s') not in ['true', '1'] or is_granted('%s'))",
                    SqlParameter::KEY,
                    SqlParameter::ROLE,
                );
                $security = $operation->getSecurity();
                $operations->add(
                    $name,
                    $operation
                        ->withParameters($parameters)
                        ->withSecurity(
                            $security === null || $security === ''
                                ? $condition
                                : $condition . ' and (' . $security . ')',
                        ),
                );
            }
            $collection[$index] = $resource->withOperations($operations);
        }
        return $collection;
    }
}
