<?php

declare(strict_types=1);
/**
 * Trait pour les recherches LIKE
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Repository\Trait;

trait LikeQueryTrait
{
    /**
     * Échappe les jokers % et _ d'une valeur recherchée avec LIKE
     * @param string $value
     * @return string
     */
    protected function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
