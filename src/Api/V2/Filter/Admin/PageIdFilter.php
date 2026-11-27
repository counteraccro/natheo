<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre exact sur l'identifiant d'une page
 */

namespace App\Api\V2\Filter\Admin;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;

final class PageIdFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * Nom du paramètre, page étant déjà pris par la pagination
     * @var string
     */
    public const string PARAMETER = 'pageId';

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'integer', 'minimum' => 1];
    }

    /**
     * Page demandée, null sans filtre
     * @param Operation $operation
     * @return int|null
     */
    public static function getValue(Operation $operation): ?int
    {
        $value = $operation->getParameters()?->get(self::PARAMETER)?->getValue(null);
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }
}
