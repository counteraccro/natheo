<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tri d'une collection d'administration par order[<champ>]=asc|desc, sur les champs déclarés par l'opération
 */

namespace App\Api\V2\Filter\Admin;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use App\Api\V2\Filter\OrderFilter;

final class AdminOrderFilter implements FilterInterface, JsonSchemaFilterInterface
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
        return ['type' => 'string', 'enum' => OrderFilter::getApiNames()];
    }

    /**
     * Premier tri demandé, null si aucun
     * @param Operation $operation
     * @return array{field: string, direction: \SortDirection}|null
     */
    public static function getValue(Operation $operation): ?array
    {
        foreach ($operation->getParameters() ?? [] as $parameter) {
            $direction = OrderFilter::fromApiName($parameter->getValue(null));
            if ($parameter->getFilter() !== self::class || $direction === null || $parameter->getProperty() === null) {
                continue;
            }
            return ['field' => $parameter->getProperty(), 'direction' => $direction];
        }
        return null;
    }
}
