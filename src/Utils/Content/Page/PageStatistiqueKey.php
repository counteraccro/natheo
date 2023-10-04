<?php
/**
 * Constantes pour les clés des statistiques
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Content\Page;

class PageStatistiqueKey
{
    const KEY_PAGE_NB_VISITEUR = 'PAGE_NB_VISITEUR';

    const KEY_PAGE_NB_READ = 'PAGE_NB_READ';

    /**
     * Retourne la liste de constantes de la class
     * @return array
     */
    public static function getConstants() : array
    {
        // "static::class" here does the magic
        $reflectionClass = new \ReflectionClass(static::class);
        return $reflectionClass->getConstants();
    }
}
