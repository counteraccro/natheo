<?php

declare(strict_types=1);
/**
 * Mise en forme lisible d'une taille de fichier
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Formatter\Global;

class FileSizeFormatter
{
    /**
     * Retourne une taille en octets sous une forme lisible (ex. « 1.5 Mo »)
     * @param int $octet
     * @return string
     */
    public static function format(int $octet): string
    {
        if ($octet < 1000) {
            return $octet . ' octet';
        }
        if ($octet < 1000000) {
            return round($octet / 1024, 2) . ' Ko';
        }
        if ($octet < 1000000000) {
            return round($octet / (1024 * 1024), 2) . ' Mo';
        }
        return round($octet / (1024 * 1024 * 1024), 2) . ' Go';
    }
}
