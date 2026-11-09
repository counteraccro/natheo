<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Renseigne les langues acceptées par LocaleParameter à partir du paramètre app.supported_locales
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Api\V2\Parameter\LocaleParameter;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

// Avant la validation des paramètres (priorité 1000), qui déduit ses contraintes du schéma
#[AsDecorator(decorates: 'api_platform.metadata.resource.metadata_collection_factory', priority: 1001)]
final readonly class LocaleParameterMetadataFactory implements ResourceMetadataCollectionFactoryInterface
{
    public function __construct(
        #[AutowireDecorated] private ResourceMetadataCollectionFactoryInterface $decorated,
        #[Autowire('%app.supported_locales%')] private string $supportedLocales,
    ) {}

    /**
     * @param string $resourceClass
     * @return ResourceMetadataCollection
     */
    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $collection = $this->decorated->create($resourceClass);
        $locales = explode('|', $this->supportedLocales);

        foreach ($collection as $i => $resource) {
            $operations = new Operations();
            foreach ($resource->getOperations() ?? [] as $name => $operation) {
                $parameters = $operation->getParameters();
                if ($parameters !== null) {
                    $operation = $operation->withParameters($this->withLocales($parameters, $locales));
                }
                $operations->add($name, $operation);
            }
            $collection[$i] = $resource->withOperations($operations);
        }
        return $collection;
    }

    /**
     * Copie des paramètres où chaque LocaleParameter reçoit la liste des langues
     * @param Parameters $parameters
     * @param string[] $locales
     * @return Parameters
     */
    private function withLocales(Parameters $parameters, array $locales): Parameters
    {
        $result = new Parameters();
        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof LocaleParameter) {
                $parameter = $parameter->withSchema(['type' => 'string', 'enum' => $locales]);
            }
            $result->add($key, $parameter);
        }
        return $result;
    }
}
