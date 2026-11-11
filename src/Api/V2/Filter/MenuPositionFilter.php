<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre des menus par position : valeurs acceptées issues de MenuPosition
 */

namespace App\Api\V2\Filter;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use App\Enum\Admin\Content\Menu\MenuPosition;

final class MenuPositionFilter implements FilterInterface, JsonSchemaFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    /**
     * @param Parameter $parameter
     * @return array<string, mixed>
     */
    public function getSchema(Parameter $parameter): array
    {
        return [
            'type' => 'string',
            'enum' => MenuPosition::getSlugs(),
        ];
    }

    /**
     * Retourne la position demandée sur l'opération, null si absente
     * @param Operation $operation
     * @return MenuPosition|null
     */
    public static function getValue(Operation $operation): ?MenuPosition
    {
        $value = $operation->getParameters()?->get('position')?->getValue(null);
        return MenuPosition::tryFromSlug(is_string($value) ? $value : null);
    }
}
