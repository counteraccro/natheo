<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Fixture pour les médias folders
 */

namespace App\Tests\Helper\Fixtures\Content\Media;

use App\Entity\Admin\Content\Media\MediaFolder;
use App\Tests\Helper\FakerTrait;

trait MediaFolderFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'un média folder
     * @param MediaFolder|null $parent
     * @param array $customData
     * @param bool $persist
     * @return MediaFolder
     */
    public function createMediaFolder(?MediaFolder $parent = null, array $customData = [], bool $persist = true): MediaFolder
    {
        $path = DIRECTORY_SEPARATOR;
        if ($parent !== null) {
            if ($parent->getPath() === DIRECTORY_SEPARATOR) {
                $path = $parent->getPath() . $parent->getName();
            } else {
                $path = $parent->getPath() . DIRECTORY_SEPARATOR . $parent->getName();
            }

        }

        $data = [
            'name' => self::getFaker()->slug(2),
            'path' => $path,
            'disabled' => self::getFaker()->boolean(),
            'trash' => self::getFaker()->boolean(),
            'parent' => $parent,
        ];

        $mediaFolder = $this->initEntity(MediaFolder::class, array_merge($data, $customData));
        $parent?->addChild($mediaFolder);

        if ($persist) {
            $this->persistAndFlush($mediaFolder);
        }
        return $mediaFolder;
    }
}