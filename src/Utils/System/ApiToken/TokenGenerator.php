<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Génération aléatoire des tokens API
 */
namespace App\Utils\System\ApiToken;

use Symfony\Component\String\ByteString;

class TokenGenerator
{
    /**
     * Génère un nouveau token : API_TOKEN_SEGMENT segments aléatoires de API_TOKEN_LENGTH caractères séparés par un "."
     * @return string
     */
    public static function generate(): string
    {
        $segments = [];
        for ($i = 0; $i < ApiTokenConst::API_TOKEN_SEGMENT; $i++) {
            $segments[] = ByteString::fromRandom(ApiTokenConst::API_TOKEN_LENGTH)->toString();
        }
        return implode('.', $segments);
    }
}
