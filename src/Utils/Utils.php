<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Recense des fonctions utiles
 */
namespace App\Utils;

class Utils
{
    /**
     * Converti la taille de fichier en octet en Ko, Mo, GO
     * @param int $octet
     * @return string
     */
    public static function getSizeName(int $octet): string
    {
        $unite = array(' octet', ' Ko', ' Mo', ' Go');

        if ($octet < 1000) {
            return $octet . $unite[0];
        } else {
            if ($octet < 1000000) {
                $ko = round($octet / 1024, 2);
                return $ko . $unite[1];
            } else {
                if ($octet < 1000000000) {
                    $mo = round($octet / (1024 * 1024), 2);
                    return $mo . $unite[2];
                } else {
                    $go = round($octet / (1024 * 1024 * 1024), 2);
                    return $go . $unite[3];
                }
            }
        }
    }

    /**
     * Convertie une string en camelCase vers snake_case
     * @param string $camelCase
     * @return string
     */
    public static function convertCamelCaseToSnakeCase(string $camelCase) {
        $result = '';

        for ($i = 0; $i < strlen($camelCase); $i++) {
            $char = $camelCase[$i];

            if (ctype_upper($char)) {
                $result .= '_' . strtolower($char);
            } else {
                $result .= $char;
            }
        }
        return ltrim($result, '_');
    }
}
