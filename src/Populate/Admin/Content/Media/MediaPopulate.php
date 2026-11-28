<?php

declare(strict_types=1);
/**
 * Remplit un média à partir du fichier rangé dans le stockage
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Populate\Admin\Content\Media;

use App\Dto\Media\StoredMediaFile;
use App\Entity\Admin\Content\Media\Media;
use App\Enum\Admin\Content\Media\MediaType;

class MediaPopulate
{
    /**
     * Copie dans le média la clé, le type, le poids, les dimensions et l'empreinte du fichier rangé
     * @param Media $media
     * @param StoredMediaFile $file
     * @param string $originalName nom du fichier envoyé
     * @return Media
     */
    public static function fromStoredFile(Media $media, StoredMediaFile $file, string $originalName): Media
    {
        return $media
            ->setName(mb_substr($originalName, 0, 255))
            ->setPath($file->key)
            ->setMimeType($file->mimeType)
            ->setType(MediaType::fromMimeType($file->mimeType))
            ->setExtension(strtolower(pathinfo($file->key, PATHINFO_EXTENSION)))
            ->setSize($file->size)
            ->setWidth($file->width)
            ->setHeight($file->height)
            ->setHash($file->hash)
            ->setHasThumbnail($file->hasThumbnail);
    }
}
