<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixture pour les médias, fichiers rangés dans le stockage de test
 */

namespace App\Tests\Helper\Fixtures\Content\Media;

use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Entity\Admin\Content\Media\MediaTranslation;
use App\Entity\Admin\System\User;
use App\Populate\Admin\Content\Media\MediaPopulate;
use App\Service\Global\Media\MediaStorage;
use App\Tests\Helper\FakerTrait;
use App\Tests\Helper\Media\TestImage;

trait MediaFixturesTrait
{
    use FakerTrait;

    /**
     * Créer un média dont le fichier est rangé dans le stockage de test
     * @param MediaFolder|null $mediaFolder
     * @param User|null $user contributeur créé si null
     * @param array $customData
     * @param string|null $content contenu du fichier, une image PNG si null
     * @param string $fileName nom du fichier envoyé
     * @return Media
     */
    public function createMedia(
        ?MediaFolder $mediaFolder = null,
        ?User $user = null,
        array $customData = [],
        ?string $content = null,
        string $fileName = 'image.png',
    ): Media {
        $source = TestImage::file($content ?? TestImage::png());
        try {
            $stored = $this->container->get(MediaStorage::class)->store($source, $fileName);
        } finally {
            @unlink($source);
        }

        $media = MediaPopulate::fromStoredFile(new Media(), $stored, $fileName)
            ->setMediaFolder($mediaFolder)
            ->setUser($user ?? $this->createUserContributeur());
        $media->addMediaTranslation(
            (new MediaTranslation())
                ->setLocale('fr')
                ->setTitle(self::getFaker()->text(50))
                ->setAlt(self::getFaker()->text(80)),
        );
        $this->initEntity(Media::class, $customData, $media);
        $this->persistAndFlush($media);
        return $media;
    }

    /**
     * Vide le stockage de test
     * @return void
     */
    public function clearMediaStorage(): void
    {
        $this->container->get(MediaStorage::class)->clear();
    }
}
