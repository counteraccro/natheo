<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Bloque la construction du cache (cache:clear, déploiement) quand config/cms/overwrite.yaml vise une opération inconnue
 */

namespace App\Api\V2\Metadata;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final readonly class OverwriteCacheWarmer implements CacheWarmerInterface
{
    public function __construct(private OverwriteValidator $validator) {}

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return false;
    }

    /**
     * @param string $cacheDir
     * @param string|null $buildDir
     * @return string[]
     */
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->validator->validate();
        return [];
    }
}
