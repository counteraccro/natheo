<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Sens de tri d'une collection : valeurs acceptées issues de SortDirection
 */

namespace App\Api\V2\Filter;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;

final class OrderFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Nom du paramètre déclaré sur les opérations
     * @var string
     */
    public const string PARAMETER = 'order';

    /**
     * Sens de tri appliqué quand le paramètre est absent
     * @var \SortDirection
     */
    public const \SortDirection DEFAULT = \SortDirection::Descending;

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return [
            'type' => 'string',
            'enum' => array_map(self::toApiName(...), \SortDirection::cases()),
            'default' => self::toApiName(self::DEFAULT),
        ];
    }

    /**
     * Retourne le sens de tri demandé sur l'opération, DEFAULT si absent
     * @param Operation $operation
     * @return \SortDirection
     */
    public static function getValue(Operation $operation): \SortDirection
    {
        $value = $operation->getParameters()?->get(self::PARAMETER)?->getValue(null);
        foreach (\SortDirection::cases() as $direction) {
            if ($value === self::toApiName($direction)) {
                return $direction;
            }
        }
        return self::DEFAULT;
    }

    /**
     * Valeur du sens de tri dans l'API
     * @param \SortDirection $direction
     * @return string
     */
    private static function toApiName(\SortDirection $direction): string
    {
        return match ($direction) {
            \SortDirection::Ascending => 'asc',
            \SortDirection::Descending => 'desc',
        };
    }
}
