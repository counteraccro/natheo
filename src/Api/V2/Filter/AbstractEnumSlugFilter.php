<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des filtres dont les valeurs sont les slugs d'un enum (EnumSlugTrait)
 */

namespace App\Api\V2\Filter;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;

abstract class AbstractEnumSlugFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Enum dont les slugs sont acceptés
     * @return class-string
     */
    abstract protected static function getEnum(): string;

    /**
     * Nom du paramètre déclaré sur les opérations
     * @return string
     */
    abstract protected static function getParameterName(): string;

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'string', 'enum' => static::getEnum()::getSlugs()];
    }

    /**
     * Slug demandé sur l'opération, null si absent
     * @param Operation $operation
     * @return string|null
     */
    protected static function getSlug(Operation $operation): ?string
    {
        $value = $operation->getParameters()?->get(static::getParameterName())?->getValue(null);
        return is_string($value) ? $value : null;
    }
}
