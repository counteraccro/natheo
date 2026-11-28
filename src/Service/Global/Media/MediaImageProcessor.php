<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Traitements GD des images de la médiathèque : orientation, métadonnées, miniatures et couvertures
 */

namespace App\Service\Global\Media;

class MediaImageProcessor
{
    /**
     * Types MIME des images traitées par GD
     */
    public const array IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /**
     * Types MIME acceptés pour une couverture de vidéo
     */
    public const array COVER_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Côté maximal d'une miniature, en pixels
     */
    public const int THUMBNAIL_SIZE = 480;

    /**
     * Côté maximal d'une couverture rangée, en pixels
     */
    public const int COVER_SIZE = 1280;

    /**
     * Côté maximal d'une couverture envoyée, en pixels
     */
    public const int COVER_MAX_SOURCE_SIZE = 4096;

    /**
     * Qualité des images WebP produites
     */
    private const int WEBP_QUALITY = 80;

    /**
     * Qualité d'un JPEG réencodé après redressement
     */
    private const int JPEG_QUALITY = 90;

    public function __construct(private readonly MediaMetadataStripper $metadataStripper) {}

    /**
     * Redresse une photo selon son orientation EXIF et retire ses métadonnées, en place
     * @param string $path fichier local
     * @param string $mimeType type MIME réel
     * @return void
     */
    public function normalize(string $path, string $mimeType): void
    {
        if (!in_array($mimeType, ['image/jpeg', 'image/webp'], true)) {
            return;
        }
        $bytes = (string) file_get_contents($path);

        if ($mimeType === 'image/jpeg') {
            $orientation = $this->metadataStripper->jpegOrientation($bytes);
            if ($orientation !== MediaMetadataStripper::ORIENTATION_NORMAL) {
                $image = $this->load($path, $mimeType);
                if ($image !== null) {
                    // Réencodé par GD : l'image produite n'a plus de métadonnées
                    imagejpeg($this->orient($image, $orientation), $path, self::JPEG_QUALITY);
                    return;
                }
            }
        }

        $stripped = $this->metadataStripper->strip($bytes, $mimeType);
        if ($stripped !== null) {
            file_put_contents($path, $stripped);
            return;
        }

        // Structure illisible par le lecteur de segments : réencodée pour ne garder aucune métadonnée
        $image = $this->load($path, $mimeType);
        if ($image !== null) {
            $mimeType === 'image/jpeg'
                ? imagejpeg($image, $path, self::JPEG_QUALITY)
                : imagewebp($image, $path, self::WEBP_QUALITY);
        }
    }

    /**
     * Largeur et hauteur d'une image, null pour un autre fichier
     * @param string $path
     * @return array{0: int, 1: int}|null
     */
    public function dimensions(string $path): ?array
    {
        $size = @getimagesize($path);
        if ($size === false || $size[0] <= 0 || $size[1] <= 0) {
            return null;
        }
        return [$size[0], $size[1]];
    }

    /**
     * Miniature WebP d'une image, null si GD ne sait pas la lire
     * @param string $path
     * @param string $mimeType
     * @return string|null
     */
    public function thumbnail(string $path, string $mimeType): ?string
    {
        $image = $this->load($path, $mimeType);
        return $image === null ? null : $this->toWebp($this->fit($image, self::THUMBNAIL_SIZE));
    }

    /**
     * Couverture d'une vidéo contrôlée puis réencodée en WebP
     * @param string $path image envoyée
     * @return string
     * @throws MediaFileException
     */
    public function cover(string $path): string
    {
        $mimeType = is_file($path) ? (string) mime_content_type($path) : '';
        if (!in_array($mimeType, self::COVER_MIME_TYPES, true)) {
            throw new MediaFileException(MediaFileException::COVER_INVALID);
        }
        $dimensions = $this->dimensions($path);
        if ($dimensions === null) {
            throw new MediaFileException(MediaFileException::COVER_INVALID);
        }
        if (max($dimensions) > self::COVER_MAX_SOURCE_SIZE) {
            throw new MediaFileException(MediaFileException::COVER_TOO_LARGE);
        }
        $image = $this->load($path, $mimeType);
        if ($image === null) {
            throw new MediaFileException(MediaFileException::COVER_INVALID);
        }
        return $this->toWebp($this->fit($image, self::COVER_SIZE));
    }

    /**
     * Image GD d'un fichier, null si GD ne sait pas le lire
     * @param string $path
     * @param string $mimeType
     * @return \GdImage|null
     */
    private function load(string $path, string $mimeType): ?\GdImage
    {
        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => false,
        };
        return $image instanceof \GdImage ? $image : null;
    }

    /**
     * Image tournée et retournée pour être affichée dans le bon sens
     * @param \GdImage $image
     * @param int $orientation orientation EXIF, de 2 à 8
     * @return \GdImage
     */
    private function orient(\GdImage $image, int $orientation): \GdImage
    {
        // imagerotate() tourne dans le sens inverse des aiguilles d'une montre
        $rotation = match ($orientation) {
            3 => 180,
            5, 6 => 270,
            7, 8 => 90,
            default => 0,
        };
        if ($rotation !== 0) {
            $image = imagerotate($image, $rotation, 0);
        }
        if (in_array($orientation, [2, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }
        if ($orientation === 4) {
            imageflip($image, IMG_FLIP_VERTICAL);
        }
        return $image;
    }

    /**
     * Image réduite pour tenir dans un carré, jamais agrandie, transparence gardée
     * @param \GdImage $image
     * @param int $size côté du carré
     * @return \GdImage
     */
    private function fit(\GdImage $image, int $size): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if (max($width, $height) <= $size) {
            imagepalettetotruecolor($image);
            imagesavealpha($image, true);
            return $image;
        }

        $ratio = $size / max($width, $height);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
        imagecopyresampled($target, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        return $target;
    }

    /**
     * Contenu WebP d'une image
     * @param \GdImage $image
     * @return string
     */
    private function toWebp(\GdImage $image): string
    {
        ob_start();
        imagewebp($image, null, self::WEBP_QUALITY);
        return (string) ob_get_clean();
    }
}
