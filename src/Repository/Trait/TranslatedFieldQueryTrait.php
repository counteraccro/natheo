<?php

declare(strict_types=1);
/**
 * Trait pour lire un champ traduit dans une requête, avec repli sur la langue par défaut
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Repository\Trait;

use Doctrine\ORM\QueryBuilder;

trait TranslatedFieldQueryTrait
{
    /**
     * Joint la traduction de $locale (alias translation) et retourne l'expression du champ ; la traduction de
     * $defaultLocale (alias default_translation) n'est jointe que si les deux langues diffèrent
     * @param QueryBuilder $qb
     * @param string $association association des traductions (tag.tagTranslations)
     * @param string $field champ traduit (label, title)
     * @param string $locale
     * @param string $defaultLocale
     * @return string
     */
    protected function joinTranslatedField(
        QueryBuilder $qb,
        string $association,
        string $field,
        string $locale,
        string $defaultLocale,
    ): string {
        $qb->leftJoin($association, 'translation', 'WITH', 'translation.locale = :locale')->setParameter(
            'locale',
            $locale,
        );
        if ($locale === $defaultLocale) {
            return 'translation.' . $field;
        }
        $qb->leftJoin(
            $association,
            'default_translation',
            'WITH',
            'default_translation.locale = :default_locale',
        )->setParameter('default_locale', $defaultLocale);
        return 'COALESCE(translation.' . $field . ', default_translation.' . $field . ')';
    }
}
