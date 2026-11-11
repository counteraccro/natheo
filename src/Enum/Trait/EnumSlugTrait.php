<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Slug des cas d'un enum, utilisé par l'API et les URL du front
 */

namespace App\Enum\Trait;

trait EnumSlugTrait
{
    /**
     * Slug du cas : son nom en minuscules (ex : wait_validation)
     * @return string
     */
    public function getSlug(): string
    {
        return strtolower($this->name);
    }

    /**
     * Retourne le cas correspondant au slug, null si inconnu
     * @param string|null $slug
     * @return self|null
     */
    public static function tryFromSlug(?string $slug): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->getSlug() === $slug) {
                return $case;
            }
        }
        return null;
    }

    /**
     * Slugs de tous les cas
     * @return string[]
     */
    public static function getSlugs(): array
    {
        return array_map(fn(self $case) => $case->getSlug(), self::cases());
    }
}
