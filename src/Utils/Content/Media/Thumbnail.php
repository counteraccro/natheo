<?php
/**
 * Génération de miniatures
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Media;

use Symfony\Component\String\ByteString;

class Thumbnail
{
    private string $rootThumbnailPath = '';

    public function __construct(string $rootThumbnailPath)
    {
        $this->rootThumbnailPath = $rootThumbnailPath;
    }

    /**
     * Créer une miniature en fonction d'une image et retourne son nom
     * @param $pathImg
     * @param $ext
     * @param $thumbWidth
     * @return string|null
     */
    public function create($pathImg, $ext, $thumbWidth = 100)
    {
        $sourceImage = $this->getGdImage($pathImg, $ext);
        if ($sourceImage === false || $sourceImage === null) {
            return null;
        }

        $orgWidth = imagesx($sourceImage);
        $orgHeight = imagesy($sourceImage);
        $thumbHeight = floor($orgHeight * ($thumbWidth / $orgWidth));
        $destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $orgWidth, $orgHeight);

        $imageName = ByteString::fromRandom()->toString();
        imagejpeg($destImage, $this->rootThumbnailPath . DIRECTORY_SEPARATOR . $imageName . '.jpeg');
        return $imageName;
    }

    /**
     * Retourne une GdImage en fonction de l'extension d'une image
     * @param string $path
     * @param string $ext
     * @return false|\GdImage|resource|null
     */
    private function getGdImage(string $path, string $ext)
    {
        return match ($ext) {
            'jpg' => imagecreatefromjpeg($path),
            'png' => imagecreatefrompng($path),
            'gif' => imagecreatefromgif($path),
            default => null,
        };
    }
}
