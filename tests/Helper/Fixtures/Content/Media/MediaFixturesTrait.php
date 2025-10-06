<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture pour les médias
 */

namespace App\Tests\Helper\Fixtures\Content\Media;

use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;
use App\Utils\Content\Media\MediaConst;

trait MediaFixturesTrait
{
    use FakerTrait;

    /**
     * Créer un média
     * @param MediaFolder|null $mediaFolder
     * @param User|null $user
     * @param array $customData
     * @param bool $persist
     * @return Media
     */
    public function createMedia(
        ?MediaFolder $mediaFolder = null,
        ?User $user = null,
        array $customData = [],
        bool $persist = true,
    ): Media {
        $name = self::getFaker()->slug(2);
        $extension = 'jpg';
        $path = DIRECTORY_SEPARATOR . $name . '.' . $extension;
        if ($mediaFolder !== null) {
            if ($mediaFolder->getParent() !== null) {
                $path = $mediaFolder->getPath() . DIRECTORY_SEPARATOR . $name . '.' . $extension;
            } else {
                $path = $mediaFolder->getPath() . $name . '.' . $extension;
            }
        }

        if ($user === null) {
            $user = $this->createUserContributeur();
        }

        $types = [MediaConst::MEDIA_TYPE_IMG, MediaConst::MEDIA_TYPE_FILE];

        $data = [
            'name' => $name,
            'title' => self::getFaker()->text(100),
            'description' => self::getFaker()->text(),
            'type' => $types[array_rand($types)],
            'extension' => $extension,
            'size' => self::getFaker()->randomNumber(6, false),
            'thumbnail' => self::getFaker()->bothify('#??#?##??'),
            'path' => $path,
            'webPath' => '/assets/natheotheque' . $path,
            'disabled' => self::getFaker()->boolean(),
            'trash' => self::getFaker()->boolean(),
            'mediaFolder' => $mediaFolder,
            'user' => $user,
        ];

        $media = $this->initEntity(Media::class, array_merge($data, $customData));
        $mediaFolder?->addMedia($media);

        if ($persist) {
            $this->persistAndFlush($media);
        }
        return $media;
    }
}
