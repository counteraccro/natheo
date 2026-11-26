<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre des pages par catégorie : valeurs acceptées issues de PageCategory
 */

namespace App\Api\V2\Filter;

use ApiPlatform\Metadata\Operation;
use App\Enum\Admin\Content\Page\PageCategory;

final class PageCategoryFilter extends AbstractEnumSlugFilter
{
    /**
     * Retourne la catégorie demandée sur l'opération, null si absente
     * @param Operation $operation
     * @return PageCategory|null
     */
    public static function getValue(Operation $operation): ?PageCategory
    {
        return PageCategory::tryFromSlug(self::getSlug($operation));
    }

    /**
     * @return class-string<PageCategory>
     */
    protected static function getEnum(): string
    {
        return PageCategory::class;
    }

    /**
     * @return string
     */
    protected static function getParameterName(): string
    {
        return 'category';
    }
}
