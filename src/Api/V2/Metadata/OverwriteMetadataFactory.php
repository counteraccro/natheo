<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Remplace le provider ou le processor des opérations surchargées dans config/cms/overwrite.yaml. Adresse, sécurité,
 * entrées et sorties restent celles de l'opération d'origine
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\DependencyInjection\Compiler\OverwriteConfigPass;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: 'api_platform.metadata.resource.metadata_collection_factory', priority: 1)]
final readonly class OverwriteMetadataFactory implements ResourceMetadataCollectionFactoryInterface
{
    /**
     * @param ResourceMetadataCollectionFactoryInterface $decorated
     * @param array<string, array<string, class-string>> $overwrites
     */
    public function __construct(
        #[AutowireDecorated] private ResourceMetadataCollectionFactoryInterface $decorated,
        #[Autowire(param: OverwriteConfigPass::API_PARAMETER)] private array $overwrites,
    ) {}

    /**
     * @param string $resourceClass
     * @return ResourceMetadataCollection
     */
    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $collection = $this->decorated->create($resourceClass);
        if ($this->overwrites === []) {
            return $collection;
        }

        foreach ($collection as $index => $resource) {
            $operations = $resource->getOperations();
            if ($resource instanceof ErrorResource || $operations === null) {
                continue;
            }
            foreach ($operations as $name => $operation) {
                if (isset($this->overwrites[$operation->getName()])) {
                    $operations->add($name, $this->apply($operation, $this->overwrites[$operation->getName()]));
                }
            }
            $collection[$index] = $resource->withOperations($operations);
        }
        return $collection;
    }

    /**
     * @param Operation $operation
     * @param array<string, class-string> $overwrite
     * @return Operation
     * @throws \LogicException si un processor est donné à une opération de lecture
     */
    private function apply(Operation $operation, array $overwrite): Operation
    {
        if (isset($overwrite[OverwriteConfigPass::PROVIDER])) {
            return $operation->withProvider($overwrite[OverwriteConfigPass::PROVIDER]);
        }
        if ($operation instanceof HttpOperation && $operation->getMethod() === HttpOperation::METHOD_GET) {
            throw new \LogicException(
                sprintf('L\'opération de lecture « %s » ne peut pas recevoir de processor', $operation->getName()),
            );
        }
        return $operation->withProcessor($overwrite[OverwriteConfigPass::PROCESSOR]);
    }
}
