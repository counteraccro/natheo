<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Vérifie que chaque opération surchargée dans config/cms/overwrite.yaml existe
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use App\DependencyInjection\Compiler\OverwriteConfigPass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class OverwriteValidator
{
    /**
     * @param ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory
     * @param ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory
     * @param array<string, array<string, class-string>> $overwrites
     */
    public function __construct(
        private ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        #[Autowire(param: OverwriteConfigPass::API_PARAMETER)] private array $overwrites,
    ) {}

    /**
     * Construit les métadonnées de toutes les ressources, ce qui applique aussi chaque surcharge
     * @param array<string, array<string, class-string>>|null $overwrites surcharges à vérifier, celles du conteneur
     * par défaut
     * @return void
     * @throws \LogicException si une opération surchargée n'existe pas
     */
    public function validate(?array $overwrites = null): void
    {
        $names = [];
        foreach ($this->resourceNameCollectionFactory->create() as $resourceClass) {
            foreach ($this->resourceMetadataCollectionFactory->create($resourceClass) as $resource) {
                foreach ($resource->getOperations() ?? [] as $operation) {
                    $names[(string) $operation->getName()] = true;
                }
            }
        }

        $unknown = array_keys(array_diff_key($overwrites ?? $this->overwrites, $names));
        if ($unknown !== []) {
            throw new \LogicException(
                sprintf('Opération inconnue dans config/cms/overwrite.yaml : %s', implode(', ', $unknown)),
            );
        }
    }
}
