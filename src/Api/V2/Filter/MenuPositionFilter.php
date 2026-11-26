<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre des menus par position : valeurs acceptées issues de MenuPosition
 */

namespace App\Api\V2\Filter;

use ApiPlatform\Metadata\Operation;
use App\Enum\Admin\Content\Menu\MenuPosition;

final class MenuPositionFilter extends AbstractEnumSlugFilter
{
    /**
     * Retourne la position demandée sur l'opération, null si absente
     * @param Operation $operation
     * @return MenuPosition|null
     */
    public static function getValue(Operation $operation): ?MenuPosition
    {
        return MenuPosition::tryFromSlug(self::getSlug($operation));
    }

    /**
     * @return class-string<MenuPosition>
     */
    protected static function getEnum(): string
    {
        return MenuPosition::class;
    }

    /**
     * @return string
     */
    protected static function getParameterName(): string
    {
        return 'position';
    }
}
