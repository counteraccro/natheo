<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Paramètre de requête « category » des pages de l'API v2, valeurs issues de PageCategory
 */

namespace App\Api\V2\Parameter;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\QueryParameter;
use App\Enum\Admin\Content\Page\PageCategory;

final class PageCategoryParameter extends QueryParameter
{
    public function __construct()
    {
        parent::__construct(
            schema: [
                'type' => 'string',
                'enum' => array_map(fn(PageCategory $category) => $category->getSlug(), PageCategory::cases()),
            ],
            description: 'Catégorie des pages',
        );
    }

    /**
     * Retourne la catégorie demandée sur l'opération, null si absente
     * @param Operation $operation
     * @return PageCategory|null
     */
    public static function getValueFrom(Operation $operation): ?PageCategory
    {
        $value = $operation->getParameters()?->get('category', self::class)?->getValue(null);
        foreach (PageCategory::cases() as $category) {
            if ($category->getSlug() === $value) {
                return $category;
            }
        }
        return null;
    }
}
