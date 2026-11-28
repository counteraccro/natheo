<?php

declare(strict_types=1);
/**
 * Images de test : moitié gauche rouge, moitié droite bleue, avec EXIF (orientation, position GPS) au besoin
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Helper\Media;

class TestImage
{
    /**
     * Position GPS écrite dans les EXIF de test, cherchée dans le fichier rangé
     */
    public const string GPS_MARKER = 'GPSDATA-48.8566N';

    /**
     * JPEG avec un bloc EXIF (orientation et position GPS) et un commentaire
     * @param int $width
     * @param int $height
     * @param int|null $orientation orientation EXIF, aucun EXIF si null
     * @return string
     */
    public static function jpeg(int $width = 40, int $height = 20, ?int $orientation = 1): string
    {
        ob_start();
        imagejpeg(self::image($width, $height), null, 95);
        $jpeg = (string) ob_get_clean();
        if ($orientation === null) {
            return $jpeg;
        }

        $exif = "Exif\0\0" . self::tiff($orientation);
        $comment = 'Photo prise avec ' . self::GPS_MARKER;
        return "\xff\xd8" .
            "\xff\xe1" .
            pack('n', strlen($exif) + 2) .
            $exif .
            "\xff\xfe" .
            pack('n', strlen($comment) + 2) .
            $comment .
            substr($jpeg, 2);
    }

    /**
     * WebP avec en-tête étendu, bloc EXIF et bloc XMP
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function webp(int $width = 40, int $height = 20): string
    {
        ob_start();
        imagewebp(self::image($width, $height), null, 90);
        $webp = (string) ob_get_clean();
        $imageChunks = substr($webp, 12);

        $vp8x =
            'VP8X' .
            pack('V', 10) .
            chr(0x0c) .
            "\0\0\0" .
            substr(pack('V', $width - 1), 0, 3) .
            substr(pack('V', $height - 1), 0, 3);
        $chunks = $vp8x . $imageChunks . self::chunk('EXIF', self::tiff(1)) . self::chunk('XMP ', self::GPS_MARKER);

        return 'RIFF' . pack('V', strlen($chunks) + 4) . 'WEBP' . $chunks;
    }

    /**
     * PNG transparent
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function png(int $width = 40, int $height = 20): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        ob_start();
        imagepng($image);
        return (string) ob_get_clean();
    }

    /**
     * Écrit un contenu dans un fichier temporaire et retourne son chemin
     * @param string $bytes
     * @return string
     */
    public static function file(string $bytes): string
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'test-media');
        file_put_contents($path, $bytes);
        return $path;
    }

    /**
     * Couleur dominante d'un pixel : red ou blue
     * @param string $path
     * @param int $x
     * @param int $y
     * @return string
     */
    public static function colorAt(string $path, int $x, int $y): string
    {
        $image = imagecreatefromstring((string) file_get_contents($path));
        $rgb = imagecolorsforindex($image, imagecolorat($image, $x, $y));
        return $rgb['red'] > $rgb['blue'] ? 'red' : 'blue';
    }

    /**
     * Image moitié rouge à gauche, moitié bleue à droite
     * @param int $width
     * @param int $height
     * @return \GdImage
     */
    private static function image(int $width, int $height): \GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, intdiv($width, 2) - 1, $height - 1, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle(
            $image,
            intdiv($width, 2),
            0,
            $width - 1,
            $height - 1,
            imagecolorallocate($image, 0, 0, 255),
        );
        return $image;
    }

    /**
     * Bloc TIFF petit-boutiste : orientation et pointeur vers un répertoire GPS
     * @param int $orientation
     * @return string
     */
    private static function tiff(int $orientation): string
    {
        $gpsOffset = 8 + 2 + 2 * 12 + 4;
        $ifd0 =
            pack('v', 2) .
            pack('vvVv', 0x0112, 3, 1, $orientation) .
            "\0\0" .
            pack('vvVV', 0x8825, 4, 1, $gpsOffset) .
            pack('V', 0);
        $gps = pack('v', 1) . pack('vvVV', 0x0001, 2, 2, 0) . pack('V', 0) . self::GPS_MARKER;

        return 'II' . pack('v', 0x2a) . pack('V', 8) . $ifd0 . $gps;
    }

    /**
     * Bloc RIFF complété à une longueur paire
     * @param string $fourcc
     * @param string $data
     * @return string
     */
    private static function chunk(string $fourcc, string $data): string
    {
        return $fourcc . pack('V', strlen($data)) . $data . (strlen($data) % 2 === 1 ? "\0" : '');
    }
}
