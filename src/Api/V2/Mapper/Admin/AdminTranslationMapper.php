<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traductions d'une entité converties dans l'ordre des langues du site, une langue sans traduction étant absente
 */

namespace App\Api\V2\Mapper\Admin;

final readonly class AdminTranslationMapper
{
    /**
     * @template T
     * @param string[] $locales langues du site
     * @param iterable<object> $translations traductions de l'entité, avec getLocale()
     * @param callable(object, string): T $map traduction et sa langue → sortie
     * @return T[]
     */
    public static function inSiteOrder(array $locales, iterable $translations, callable $map): array
    {
        $byLocale = [];
        foreach ($translations as $translation) {
            $byLocale[$translation->getLocale()] ??= $translation;
        }
        $outputs = [];
        foreach ($locales as $locale) {
            if (isset($byLocale[$locale])) {
                $outputs[] = $map($byLocale[$locale], $locale);
            }
        }
        return $outputs;
    }
}
