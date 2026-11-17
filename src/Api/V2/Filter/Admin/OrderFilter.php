<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tri d'une collection d'administration : order[<champ>]=asc|desc, champs déclarés par l'opération
 */

namespace App\Api\V2\Filter\Admin;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;

final class OrderFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Clé du paramètre déclaré sur les opérations, déclinée pour chaque champ de properties
     * @var string
     */
    public const string PARAMETER = 'order[:property]';

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'string', 'enum' => ['asc', 'desc']];
    }

    /**
     * Premier tri demandé, null si aucun
     * @param Operation $operation
     * @return array{field: string, direction: \SortDirection}|null
     */
    public static function getValue(Operation $operation): ?array
    {
        foreach ($operation->getParameters() ?? [] as $parameter) {
            $value = $parameter->getValue(null);
            if ($parameter->getFilter() !== self::class || !is_string($value) || $parameter->getProperty() === null) {
                continue;
            }
            return [
                'field' => $parameter->getProperty(),
                'direction' => $value === 'asc' ? \SortDirection::Ascending : \SortDirection::Descending,
            ];
        }
        return null;
    }
}
