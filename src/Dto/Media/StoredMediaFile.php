<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fichier rangé dans le stockage des médias
 */

namespace App\Dto\Media;

final readonly class StoredMediaFile
{
    /**
     * @param string $key clé du fichier dans le stockage
     * @param string $mimeType type MIME réel
     * @param int $size poids du fichier rangé, en octets
     * @param string $hash SHA-256 du fichier envoyé
     * @param int|null $width largeur d'une image, après redressement
     * @param int|null $height hauteur d'une image, après redressement
     * @param bool $hasThumbnail miniature produite
     */
    public function __construct(
        public string $key,
        public string $mimeType,
        public int $size,
        public string $hash,
        public ?int $width = null,
        public ?int $height = null,
        public bool $hasThumbnail = false,
    ) {}
}
