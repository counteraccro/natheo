<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Service\Admin\Content\Media;

use App\Entity\Admin\Content\Media\Media;
use App\Service\Admin\Content\Media\MediaService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\Filesystem\Filesystem;

class MediaServiceTest extends AppWebTestCase
{
    /**
     * @var MediaService
     */
    private MediaService $mediaService;

    /**
     * @var FileSystem
     */
    private Filesystem $fileSystem;

    const IMG_UNIT_TEST = 'road.jpg';


    public function setUp(): void
    {
        parent::setUp();
        $this->mediaService = $this->container->get(MediaService::class);
        $this->fileSystem = new FileSystem();
    }

    /**
     * Test méthode media
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMoveMediaFixture(): void
    {
        $this->mediaService->resetAllMedia();
        $media = $this->createMedia();
        $this->mediaService->moveMediaFixture(self::IMG_UNIT_TEST, $media);

        $result = $this->fileSystem->exists($this->mediaService->getRootPathMedia() . '/' . self::IMG_UNIT_TEST);
        $this->assertTrue($result);

        $mediaFolder = $this->createMediaFolder();
        $media = $this->createMedia($mediaFolder);
        $this->mediaService->moveMediaFixture(self::IMG_UNIT_TEST, $media);

        $result = $this->fileSystem->exists($this->mediaService->getRootPathMedia() . DIRECTORY_SEPARATOR . $mediaFolder->getName() . '/' . self::IMG_UNIT_TEST);
        $this->assertTrue($result);

    }

    /**
     * Test méthode updateMediaFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateMediaFile() :void
    {
        $this->mediaService->resetAllMedia();
        $mediaFolder = $this->createMediaFolder();
        $media = $this->createMedia($mediaFolder);
        $this->mediaService->moveMediaFixture(self::IMG_UNIT_TEST, $media);

        $this->mediaService->UpdateMediaFile($media, self::IMG_UNIT_TEST);

        /** @var Media $verif */
        $verif = $this->mediaService->findOneById(Media::class, $media->getId());
        $result = $this->fileSystem->exists($this->mediaService->getRootPathMedia() . DIRECTORY_SEPARATOR . $media->getPath());
        $this->assertTrue($result);
        $result = $this->fileSystem->exists($this->mediaService->getRootPathThumbnail() . DIRECTORY_SEPARATOR . $verif->getThumbnail());
        $this->assertTrue($result);
    }

    /**
     * Test méthode getWebPath()
     * @return void
     */
    public function testGetWebPath() :void
    {
        $mediaFolder = $this->createMediaFolder();
        $media = $this->createMedia($mediaFolder);

        $result = $this->mediaService->getWebPath($media);
        $this->assertNotNull($result);
        $this->assertStringContainsString($this->mediaService->getWebPathMedia(), $result);
    }

    /**
     * Test méthode getWebPathThumbnail()
     * @return void
     */
    public function testGetWebPathThumbnail() :void
    {
        $mediaFolder = $this->createMediaFolder();
        $media = $this->createMedia($mediaFolder);
        $result = $this->mediaService->getWebPathThumbnail($media->getThumbnail());
        $this->assertNotNull($result);

    }

    /**
     * Test méthode getALlMediaAndMediaFolderByMediaFolder()
     * @return void
     */
    public function testGetALlMediaAndMediaFolderByMediaFolder() :void
    {

    }
}