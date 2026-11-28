<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lecture de l'orientation et retrait des métadonnées (EXIF, IPTC, XMP) des JPEG et WebP sans réencoder l'image
 */

namespace App\Service\Global\Media;

class MediaMetadataStripper
{
    /**
     * Orientation EXIF normale
     */
    public const int ORIENTATION_NORMAL = 1;

    /**
     * Marqueurs JPEG retirés : APP1 (EXIF, XMP), APP13 (IPTC), commentaire
     */
    private const array JPEG_STRIPPED_MARKERS = [0xe1, 0xed, 0xfe];

    /**
     * Blocs WebP retirés
     */
    private const array WEBP_STRIPPED_CHUNKS = ['EXIF', 'XMP '];

    /**
     * Orientation EXIF d'un JPEG (1 à 8), normale si absente ou illisible
     * @param string $bytes contenu du fichier
     * @return int
     */
    public function jpegOrientation(string $bytes): int
    {
        foreach ($this->jpegSegments($bytes) ?? [] as [$marker, $data]) {
            if ($marker === 0xe1 && str_starts_with($data, "Exif\0\0")) {
                return $this->tiffOrientation(substr($data, 6));
            }
        }
        return self::ORIENTATION_NORMAL;
    }

    /**
     * Contenu du fichier sans ses métadonnées, null si sa structure est illisible
     * @param string $bytes contenu du fichier
     * @param string $mimeType type MIME réel du fichier
     * @return string|null
     */
    public function strip(string $bytes, string $mimeType): ?string
    {
        return match ($mimeType) {
            'image/jpeg' => $this->stripJpeg($bytes),
            'image/webp' => $this->stripWebp($bytes),
            default => $bytes,
        };
    }

    /**
     * JPEG sans ses segments de métadonnées
     * @param string $bytes
     * @return string|null
     */
    private function stripJpeg(string $bytes): ?string
    {
        $segments = $this->jpegSegments($bytes);
        if ($segments === null) {
            return null;
        }

        $output = "\xff\xd8";
        foreach ($segments as [$marker, $data, $raw]) {
            if (!in_array($marker, self::JPEG_STRIPPED_MARKERS, true)) {
                $output .= $raw;
            }
        }
        return $output;
    }

    /**
     * Segments d'un JPEG jusqu'au début des données d'image, la suite étant gardée en un dernier segment
     * @param string $bytes
     * @return array<int, array{0: int, 1: string, 2: string}>|null marqueur, données, segment complet
     */
    private function jpegSegments(string $bytes): ?array
    {
        $length = strlen($bytes);
        if ($length < 4 || !str_starts_with($bytes, "\xff\xd8")) {
            return null;
        }

        $segments = [];
        $position = 2;
        while ($position < $length) {
            if ($bytes[$position] !== "\xff") {
                return null;
            }
            $start = $position;
            while ($position < $length && $bytes[$position] === "\xff") {
                $position++;
            }
            if ($position >= $length) {
                return null;
            }
            $marker = ord($bytes[$position]);
            $position++;

            // Marqueurs sans longueur
            if ($marker === 0x01 || ($marker >= 0xd0 && $marker <= 0xd7)) {
                $segments[] = [$marker, '', substr($bytes, $start, $position - $start)];
                continue;
            }
            // Début des données d'image ou fin du fichier : le reste est recopié tel quel
            if ($marker === 0xda || $marker === 0xd9) {
                $segments[] = [$marker, '', substr($bytes, $start)];
                return $segments;
            }
            if ($position + 2 > $length) {
                return null;
            }
            $segmentLength = unpack('n', substr($bytes, $position, 2))[1];
            if ($segmentLength < 2 || $position + $segmentLength > $length) {
                return null;
            }
            $segments[] = [
                $marker,
                substr($bytes, $position + 2, $segmentLength - 2),
                substr($bytes, $start, $position + $segmentLength - $start),
            ];
            $position += $segmentLength;
        }
        return null;
    }

    /**
     * Orientation lue dans le premier répertoire d'un bloc TIFF
     * @param string $tiff
     * @return int
     */
    private function tiffOrientation(string $tiff): int
    {
        $order = substr($tiff, 0, 2);
        if (($order !== 'II' && $order !== 'MM') || strlen($tiff) < 8) {
            return self::ORIENTATION_NORMAL;
        }
        $short = $order === 'II' ? 'v' : 'n';
        $long = $order === 'II' ? 'V' : 'N';

        $offset = unpack($long, substr($tiff, 4, 4))[1];
        if ($offset + 2 > strlen($tiff)) {
            return self::ORIENTATION_NORMAL;
        }
        $count = unpack($short, substr($tiff, $offset, 2))[1];
        for ($i = 0; $i < $count; $i++) {
            $entry = $offset + 2 + $i * 12;
            if ($entry + 12 > strlen($tiff)) {
                break;
            }
            if (unpack($short, substr($tiff, $entry, 2))[1] === 0x0112) {
                $value = unpack($short, substr($tiff, $entry + 8, 2))[1];
                return $value >= 1 && $value <= 8 ? $value : self::ORIENTATION_NORMAL;
            }
        }
        return self::ORIENTATION_NORMAL;
    }

    /**
     * WebP sans ses blocs EXIF et XMP, indicateurs de l'en-tête étendu mis à jour
     * @param string $bytes
     * @return string|null
     */
    private function stripWebp(string $bytes): ?string
    {
        $length = strlen($bytes);
        if ($length < 12 || !str_starts_with($bytes, 'RIFF') || substr($bytes, 8, 4) !== 'WEBP') {
            return null;
        }

        $chunks = '';
        $position = 12;
        while ($position < $length) {
            if ($position + 8 > $length) {
                return null;
            }
            $fourcc = substr($bytes, $position, 4);
            $size = unpack('V', substr($bytes, $position + 4, 4))[1];
            $padded = $size + ($size % 2);
            if ($position + 8 + $size > $length) {
                return null;
            }
            $chunk = substr($bytes, $position, 8 + $padded);
            if ($fourcc === 'VP8X' && $size >= 1) {
                // Bits 3 (EXIF) et 2 (XMP) des indicateurs
                $chunk[8] = chr(ord($chunk[8]) & ~0x0c);
            }
            if (!in_array($fourcc, self::WEBP_STRIPPED_CHUNKS, true)) {
                $chunks .= $chunk;
            }
            $position += 8 + $padded;
        }

        return 'RIFF' . pack('V', strlen($chunks) + 4) . 'WEBP' . $chunks;
    }
}
