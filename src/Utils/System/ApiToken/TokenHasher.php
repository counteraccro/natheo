<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Hash des tokens d'accès (ApiToken, token utilisateur API) avant stockage
 */
namespace App\Utils\System\ApiToken;

class TokenHasher
{
    /**
     * Retourne le hash SHA-256 d'un token.
     * Les tokens sont générés aléatoirement avec une forte entropie, un hash rapide et déterministe suffit
     * et permet la recherche directe en base
     * @param string $token
     * @return string
     */
    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
