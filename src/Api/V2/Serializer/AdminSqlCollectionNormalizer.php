<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ajoute « sql » à la collection Hydra d'administration dont la requête SQL a été demandée
 */

namespace App\Api\V2\Serializer;

use ApiPlatform\State\Pagination\PartialPaginatorInterface;
use App\Api\V2\State\Provider\Admin\AdminCollectionPaginator;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsDecorator(decorates: 'api_platform.hydra.normalizer.collection', priority: -10)]
final readonly class AdminSqlCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    /**
     * Champ ajouté à la collection
     * @var string
     */
    public const string FIELD = 'sql';

    public function __construct(
        #[AutowireDecorated] private NormalizerInterface $decorated,
        private RequestStack $requestStack,
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
        $normalized = $this->decorated->normalize($data, $format, $context);
        if (!($data instanceof PartialPaginatorInterface) || ($context['api_sub_level'] ?? false)) {
            return $normalized;
        }
        $attributes = $this->requestStack->getCurrentRequest()?->attributes;
        $sql = $attributes?->get(AdminCollectionPaginator::ATTRIBUTE);
        if (is_array($normalized) && is_string($sql)) {
            $normalized[self::FIELD] = $sql;
            $attributes->remove(AdminCollectionPaginator::ATTRIBUTE);
        }
        return $normalized;
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
     * @param NormalizerInterface $normalizer
     * @return void
     */
    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        if ($this->decorated instanceof NormalizerAwareInterface) {
            $this->decorated->setNormalizer($normalizer);
        }
    }

    /**
     * @param string|null $format
     * @return array<class-string|'*'|'object'|string, bool|null>
     */
    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }
}
