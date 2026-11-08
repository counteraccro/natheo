<?php

declare(strict_types=1);
/**
 * Tri des migrations Doctrine sur le numéro de version seul
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Doctrine\Migrations;

use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\Version;

/**
 * Les migrations sont rangées par version de Nathéo (V2, V2_1…) : le tri alphabétique du nom complet placerait
 * V2_10 avant V2_2, le tri se fait donc sur le nom de la classe (VersionAAAAMMJJHHMMSS)
 */
class VersionNumberComparator implements Comparator
{
    public function compare(Version $a, Version $b): int
    {
        return strcmp($this->getClassName($a), $this->getClassName($b)) ?: strcmp((string) $a, (string) $b);
    }

    /**
     * Retourne le nom de la classe sans son namespace
     * @param Version $version
     * @return string
     */
    private function getClassName(Version $version): string
    {
        $parts = explode('\\', (string) $version);
        return end($parts);
    }
}
