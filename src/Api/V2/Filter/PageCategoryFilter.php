<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Filtre des pages par catégorie : valeurs acceptées issues de PageCategory
 */

namespace App\Api\V2\Filter;

use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use App\Enum\Admin\Content\Page\PageCategory;

final class PageCategoryFilter implements FilterInterface, JsonSchemaFilterInterface
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
            'enum' => PageCategory::getSlugs(),
        ];
    }

    /**
     * Retourne la catégorie demandée sur l'opération, null si absente
     * @param Operation $operation
     * @return PageCategory|null
     */
    public static function getValue(Operation $operation): ?PageCategory
    {
        $value = $operation->getParameters()?->get('category')?->getValue(null);
        return PageCategory::tryFromSlug(is_string($value) ? $value : null);
    }
}
