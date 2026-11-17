<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Retire les ressources d'administration de la documentation Hydra publique
 */

namespace App\Api\V2\Documentation;

use ApiPlatform\Documentation\Documentation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsDecorator(decorates: 'api_platform.hydra.normalizer.documentation')]
final readonly class AdminResourcesDocumentationNormalizer implements NormalizerInterface
{
    public function __construct(
        #[AutowireDecorated] private NormalizerInterface $decorated,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {}

    /**
     * @param mixed $data
     * @param string|null $format
     * @param array $context
     * @return array|string|int|float|bool|\ArrayObject|null
     */
    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): array|string|int|float|bool|\ArrayObject|null {
        if ($data instanceof Documentation) {
            $classes = array_filter(
                iterator_to_array($data->getResourceNameCollection()),
                fn(string $class) => !$this->isAdminResource($class),
            );
            $data = new Documentation(
                new ResourceNameCollection(array_values($classes)),
                $data->getTitle(),
                $data->getDescription(),
                $data->getVersion(),
            );
        }
        return $this->decorated->normalize($data, $format, $context);
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    /**
     * @param string|null $format
     * @return array<class-string|'*'|'object'|string, bool|null>
     */
    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    /**
     * Ressource dont toutes les opérations appartiennent à un module du back-office
     * @param string $class
     * @return bool
     */
    private function isAdminResource(string $class): bool
    {
        $count = 0;
        foreach ($this->resourceMetadataCollectionFactory->create($class) as $resource) {
            foreach ($resource->getOperations() ?? [] as $operation) {
                if (!isset($operation->getExtraProperties()[OperationSecurityMetadataFactory::MODULE])) {
                    return false;
                }
                $count++;
            }
        }
        return $count > 0;
    }
}
