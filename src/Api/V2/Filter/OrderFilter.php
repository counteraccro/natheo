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
            'enum' => self::getApiNames(),
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
        return self::fromApiName($operation->getParameters()?->get(self::PARAMETER)?->getValue(null)) ?? self::DEFAULT;
    }

    /**
     * Valeurs des sens de tri acceptées par l'API
     * @return string[]
     */
    public static function getApiNames(): array
    {
        return array_map(self::toApiName(...), \SortDirection::cases());
    }

    /**
     * Sens de tri correspondant à une valeur de l'API, null si elle n'en est pas une
     * @param mixed $value
     * @return \SortDirection|null
     */
    public static function fromApiName(mixed $value): ?\SortDirection
    {
        foreach (\SortDirection::cases() as $direction) {
            if ($value === self::toApiName($direction)) {
                return $direction;
            }
        }
        return null;
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
