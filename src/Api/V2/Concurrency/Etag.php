<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * ETag d'une ressource versionnée et comparaison avec l'en-tête If-Match
 */

namespace App\Api\V2\Concurrency;

final class Etag
{
    /**
     * ETag fort de la ressource, null si elle n'a pas de version
     * @param VersionedResourceInterface $resource
     * @return string|null
     */
    public static function of(VersionedResourceInterface $resource): ?string
    {
        $version = $resource->getVersion();
        return $version === null ? null : '"' . hash('sha256', $version) . '"';
    }

    /**
     * Indique si l'en-tête If-Match désigne la version courante, « * » accepté
     * Le préfixe W/ est ignoré car un proxy qui compresse la réponse affaiblit l'ETag (nginx gzip)
     * @param string $ifMatch
     * @param VersionedResourceInterface $resource
     * @return bool
     */
    public static function matches(string $ifMatch, VersionedResourceInterface $resource): bool
    {
        $tags = array_map(fn(string $tag) => preg_replace('#^W/#', '', trim($tag)), explode(',', $ifMatch));
        return in_array('*', $tags, true) || in_array(self::of($resource), $tags, true);
    }
}
