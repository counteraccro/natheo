<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Schéma des propriétés dont les valeurs sont les slugs d'un enum (extraProperties « enum_slugs »)
 */

namespace App\Api\V2\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: 'api_platform.metadata.property.metadata_factory')]
final readonly class EnumSlugPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /**
     * Clé d'extraProperties qui porte la classe de l'enum, dont les cas exposent getSlug()
     * @var string
     */
    public const string EXTRA_PROPERTY = 'enum_slugs';

    public function __construct(#[AutowireDecorated] private PropertyMetadataFactoryInterface $decorated) {}

    /**
     * @param string $resourceClass
     * @param string $property
     * @param array $options
     * @return ApiProperty
     */
    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $metadata = $this->decorated->create($resourceClass, $property, $options);
        $enum = $metadata->getExtraProperties()[self::EXTRA_PROPERTY] ?? null;
        if (!is_string($enum) || !enum_exists($enum) || !method_exists($enum, 'getSlug')) {
            return $metadata;
        }

        return $metadata->withSchema([
            'type' => 'string',
            'enum' => array_map(fn(\UnitEnum $case) => $case->getSlug(), $enum::cases()),
        ]);
    }
}
