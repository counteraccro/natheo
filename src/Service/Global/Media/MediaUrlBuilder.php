<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * URL publiques des fichiers de la médiathèque, calculées à la lecture
 */

namespace App\Service\Global\Media;

use App\Dto\Media\MediaStorageConfig;

class MediaUrlBuilder
{
    public function __construct(private readonly MediaStorageConfigResolver $configResolver) {}

    /**
     * URL d'un fichier
     * @param string $key clé du fichier dans le stockage
     * @return string
     */
    public function url(string $key): string
    {
        return $this->configResolver->resolve()->url($key);
    }

    /**
     * Chemin public d'un fichier, sans hôte
     * @param string $key clé du fichier dans le stockage
     * @return string
     */
    public function path(string $key): string
    {
        return $this->configResolver->resolve()->path($key);
    }

    /**
     * URL de la miniature ou de la couverture d'un fichier
     * @param string $key clé du fichier dans le stockage
     * @return string
     */
    public function thumbnailUrl(string $key): string
    {
        return $this->configResolver->resolve()->url(MediaStorageConfig::thumbnailKey($key));
    }
}
