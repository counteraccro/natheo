<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traductions d'une ressource d'administration (propriété translations, une entrée par langue avec locale)
 */

namespace App\Api\V2\Resource\Admin\Trait;

trait AdminTranslationsTrait
{
    /**
     * Valeur d'un champ dans la traduction d'une langue, null si la langue est absente
     * @param string $locale
     * @param string $field champ de la traduction (title, label)
     * @return string|null
     */
    public function getTranslationValue(string $locale, string $field): ?string
    {
        foreach ($this->translations as $translation) {
            if ($translation->locale === $locale) {
                return $translation->{$field};
            }
        }
        return null;
    }

    /**
     * Langue et champs de chaque traduction, pour l'empreinte de la version
     * @param string ...$fields
     * @return array<int, array<int, string>>
     */
    private function getTranslationsFingerprint(string ...$fields): array
    {
        return array_map(
            fn(object $translation) => [$translation->locale, ...array_map(fn($f) => $translation->{$f}, $fields)],
            $this->translations,
        );
    }
}
