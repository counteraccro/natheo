<?php

declare(strict_types=1);
/**
 * Recherche d'une traduction par locale avec repli
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Entity\Trait;

use Doctrine\Common\Collections\Collection;

/**
 * Recherche d'une traduction par locale dans une collection d'entités *Translation
 */
trait TranslationByLocaleTrait
{
    /**
     * Retourne la traduction de $locale, à défaut celle de $defaultLocale, à défaut la première disponible
     * @param Collection $translations entités disposant d'un getLocale()
     * @param string $locale locale recherchée
     * @param string|null $defaultLocale locale de repli
     * @return object|null null uniquement si la collection est vide
     */
    private function findTranslationByLocale(
        Collection $translations,
        string $locale,
        ?string $defaultLocale = null,
    ): ?object {
        $byLocale = fn(string $search) => $translations->findFirst(
            fn(int|string $key, object $translation) => $translation->getLocale() === $search,
        );

        return $byLocale($locale)
            ?? ($defaultLocale !== null ? $byLocale($defaultLocale) : null)
            ?? ($translations->first() ?: null);
    }
}
