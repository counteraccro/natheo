<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Recherche d'une collection d'administration dans les champs déclarés par l'opération (properties du paramètre)
 */

namespace App\Api\V2\Filter\Admin;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;

final class SearchFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Nom du paramètre déclaré sur les opérations
     * @var string
     */
    public const string PARAMETER = 'search';

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'string', 'maxLength' => 255];
    }

    /**
     * Texte recherché, null si absent ou vide
     * @param Operation $operation
     * @return string|null
     */
    public static function getValue(Operation $operation): ?string
    {
        $value = $operation->getParameters()?->get(self::PARAMETER)?->getValue(null);
        $value = is_string($value) ? trim($value) : '';
        return $value === '' ? null : $value;
    }

    /**
     * Champs de la ressource dans lesquels chercher
     * @param Operation $operation
     * @return string[]
     */
    public static function getFields(Operation $operation): array
    {
        return array_values($operation->getParameters()?->get(self::PARAMETER)?->getProperties() ?? []);
    }
}
