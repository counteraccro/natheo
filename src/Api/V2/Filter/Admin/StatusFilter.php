<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre du statut d'activation d'une collection d'administration, valeurs construites depuis ActivationStatus
 */

namespace App\Api\V2\Filter\Admin;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use App\Enum\Api\Admin\ActivationStatus;

final class StatusFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Nom du paramètre déclaré sur les opérations
     * @var string
     */
    public const string PARAMETER = 'status';

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'string', 'enum' => ActivationStatus::getSlugs()];
    }

    /**
     * Statut demandé, null si absent
     * @param Operation $operation
     * @return ActivationStatus|null
     */
    public static function getValue(Operation $operation): ?ActivationStatus
    {
        $value = $operation->getParameters()?->get(self::PARAMETER)?->getValue(null);
        return ActivationStatus::tryFromSlug(is_string($value) ? $value : null);
    }
}
