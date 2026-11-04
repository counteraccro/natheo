<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Génération aléatoire des tokens API
 */
namespace App\Service\Global\Token;

use Symfony\Component\String\ByteString;

class TokenGenerator
{
    /**
     * Nombre de segments d'un token
     */
    public const int SEGMENT_COUNT = 4;

    /**
     * Longueur d'un segment
     */
    public const int SEGMENT_LENGTH = 24;

    /**
     * Génère un nouveau token : API_TOKEN_SEGMENT segments aléatoires de API_TOKEN_LENGTH caractères séparés par un "."
     * @return string
     */
    public static function generate(): string
    {
        $segments = [];
        for ($i = 0; $i < self::SEGMENT_COUNT; $i++) {
            $segments[] = ByteString::fromRandom(self::SEGMENT_LENGTH)->toString();
        }
        return implode('.', $segments);
    }
}
