<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Service\Admin\Content\Media;

use App\Entity\Admin\Content\Media\Media;
use App\Entity\Admin\Content\Media\MediaFolder;
use App\Service\Admin\Content\Media\MediaService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Media\MediaConst;
use App\Utils\Content\Media\MediaFolderConst;
use App\Utils\Utils;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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

        $result = $this->fileSystem->exists(
            $this->mediaService->getRootPathMedia() .
                DIRECTORY_SEPARATOR .
                $mediaFolder->getName() .
                '/' .
                self::IMG_UNIT_TEST,
        );
        $this->assertTrue($result);
    }

    /**
     * Test méthode updateMediaFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateMediaFile(): void
    {
        $this->mediaService->resetAllMedia();
        $mediaFolder = $this->createMediaFolder();
        $media = $this->createMedia($mediaFolder);
        $this->mediaService->moveMediaFixture(self::IMG_UNIT_TEST, $media);

        $this->mediaService->UpdateMediaFile($media, self::IMG_UNIT_TEST);

        /** @var Media $verif */
        $verif = $this->mediaService->findOneById(Media::class, $media->getId());
        $result = $this->fileSystem->exists(
            $this->mediaService->getRootPathMedia() . DIRECTORY_SEPARATOR . $media->getPath(),
        );
        $this->assertTrue($result);
        $result = $this->fileSystem->exists(
            $this->mediaService->getRootPathThumbnail() . DIRECTORY_SEPARATOR . $verif->getThumbnail(),
        );
        $this->assertTrue($result);
    }

    /**
     * Test méthode getWebPath()
     * @return void
     */
    public function testGetWebPath(): void
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
    public function testGetWebPathThumbnail(): void
    {
        $mediaFolder = $this->createMediaFolder();
        $media = $this->createMedia($mediaFolder);
        $result = $this->mediaService->getWebPathThumbnail($media->getThumbnail());
        $this->assertNotNull($result);
    }

    /**
     * Test méthode getALlMediaAndMediaFolderByMediaFolder()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetALlMediaAndMediaFolderByMediaFolder(): void
    {
        $mediaFolder = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => false]);
        $this->createMedia($mediaFolder, customData: ['disabled' => false, 'trash' => false]);
        $media = $this->createMedia($mediaFolder, customData: ['disabled' => false, 'trash' => false]);
        $folder = $this->createMediaFolder($mediaFolder, customData: ['disabled' => false, 'trash' => false]);

        $result = $this->mediaService->getALlMediaAndMediaFolderByMediaFolder($mediaFolder);
        $this->assertCount(3, $result);

        $isVerif = false;
        foreach ($result as $row) {
            if ($row['type'] === 'folder') {
                $this->assertArrayHasKey('type', $row);
                $this->assertArrayHasKey('id', $row);
                $this->assertEquals($folder->getId(), $row['id']);
                $this->assertArrayHasKey('name', $row);
                $this->assertEquals($folder->getName(), $row['name']);
                $this->assertArrayHasKey('created_at', $row);
                $this->assertArrayHasKey('date', $row);
            }

            if ($row['type'] === 'media' && $row['id'] === $media->getId()) {
                $isVerif = true;
                $this->assertArrayHasKey('type', $row);
                $this->assertArrayHasKey('id', $row);
                $this->assertArrayHasKey('name', $row);
                $this->assertEquals($media->getName(), $row['name']);
                $this->assertArrayHasKey('description', $row);
                $this->assertEquals($media->getDescription(), $row['description']);
                $this->assertArrayHasKey('size', $row);
                $this->assertEquals(Utils::getSizeName($media->getSize()), $row['size']);
                $this->assertArrayHasKey('webPath', $row);
                $this->assertEquals($media->getWebPath(), $row['webPath']);
                $this->assertArrayHasKey('thumbnail', $row);
                $this->assertEquals($this->mediaService->getThumbnail($media), $row['thumbnail']);
                $this->assertArrayHasKey('created_at', $row);
                $this->assertArrayHasKey('date', $row);
            }
        }
        $this->assertTrue($isVerif);
    }

    /**
     * Test méthode getMediaByMediaFolder()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetMediaByMediaFolder(): void
    {
        $mediaFolder = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => false]);
        $this->createMedia($mediaFolder, customData: ['disabled' => false, 'trash' => false]);
        $this->createMedia($mediaFolder, customData: ['disabled' => false, 'trash' => false]);

        $result = $this->mediaService->getMediaByMediaFolder($mediaFolder);
        $this->assertNotNull($result);
        $this->assertCount(2, $result);
    }

    /**
     * Test méthode getInfoMedia()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetInfoMedia(): void
    {
        $media = $this->createMedia(customData: ['disabled' => false, 'trash' => false]);
        $result = $this->mediaService->getInfoMedia($media->getId());
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.name', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.titre', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.description', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.extension', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.user', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.emplacement', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.size', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.date_created', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey(
            $this->translator->trans('media.mediatheque.info.media.date_update', domain: 'media'),
            $result['data'],
        );
        $this->assertArrayHasKey('thumbnail', $result);
        $this->assertArrayHasKey('web_path', $result);
        $this->assertArrayHasKey('media_type', $result);
    }

    /**
     * Test méthode getThumbnail()
     * @return void
     */
    public function testGetThumbnail(): void
    {
        $media = $this->createMedia(customData: ['type' => MediaConst::MEDIA_TYPE_IMG, 'trash' => false]);
        $result = $this->mediaService->getThumbnail($media);
        $this->assertNotNull($result);
        $this->assertEquals($this->mediaService->getWebPathThumbnail($media->getThumbnail()), $result);

        $media = $this->createMedia(customData: ['type' => MediaConst::MEDIA_TYPE_FILE, 'extension' => 'pdf']);
        $result = $this->mediaService->getThumbnail($media);
        $this->assertEquals(MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file_pdf.png', $result);

        $media = $this->createMedia(customData: ['type' => MediaConst::MEDIA_TYPE_FILE, 'extension' => 'xls']);
        $result = $this->mediaService->getThumbnail($media);
        $this->assertEquals(MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file_xls.png', $result);

        $media = $this->createMedia(customData: ['type' => MediaConst::MEDIA_TYPE_FILE, 'extension' => 'csv']);
        $result = $this->mediaService->getThumbnail($media);
        $this->assertEquals(MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file_csv.png', $result);

        $media = $this->createMedia(customData: ['type' => MediaConst::MEDIA_TYPE_FILE, 'extension' => 'docx']);
        $result = $this->mediaService->getThumbnail($media);
        $this->assertEquals(MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file_doc.png', $result);

        $media = $this->createMedia(customData: ['type' => MediaConst::MEDIA_TYPE_FILE, 'extension' => 'ext']);
        $result = $this->mediaService->getThumbnail($media);
        $this->assertEquals(MediaFolderConst::PATH_WEB_NATHEO_MEDIA . 'file.png', $result);
    }

    /**
     * Test méthode move() ainsi que moveMedia() et moveFolder()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMove(): void
    {
        $this->mediaService->resetAllMedia();
        $mediaFolder = $this->createMediaFolder(customData: ['name' => 'start-folder']);
        $media = $this->createMedia($mediaFolder, customData: ['name' => self::IMG_UNIT_TEST]);
        $this->mediaService->moveMediaFixture(self::IMG_UNIT_TEST, $media);

        $mediaFolderEnd = $this->createMediaFolder(customData: ['name' => 'end-folder']);
        $this->mediaService->createFolder($mediaFolderEnd);

        $this->mediaService->move($media->getId(), 'media', $mediaFolderEnd->getId());
        $result = $this->fileSystem->exists(
            $this->mediaService->getRootPathMedia() .
                DIRECTORY_SEPARATOR .
                'end-folder' .
                DIRECTORY_SEPARATOR .
                self::IMG_UNIT_TEST,
        );
        $this->assertTrue($result);

        /** @var MediaFolder $folderVerif */
        $folderVerif = $this->mediaService->findOneById(MediaFolder::class, $mediaFolderEnd->getId());
        $this->assertCount(1, $folderVerif->getMedias());
        $media = $folderVerif->getMedias()->first();
        $this->assertEquals(
            $folderVerif->getPath() . $folderVerif->getName() . DIRECTORY_SEPARATOR . $media->getName(),
            $media->getPath(),
        );

        $this->mediaService->move($mediaFolder->getId(), 'folder', $mediaFolderEnd->getId());
        $result = $this->fileSystem->exists(
            $this->mediaService->getRootPathMedia() .
                DIRECTORY_SEPARATOR .
                'end-folder' .
                DIRECTORY_SEPARATOR .
                $mediaFolder->getName(),
        );
        $this->assertTrue($result);

        /** @var MediaFolder $folderVerif */
        $folderVerif = $this->mediaService->findOneById(MediaFolder::class, $mediaFolderEnd->getId());
        $this->assertCount(1, $folderVerif->getChildren());
        $children = $folderVerif->getChildren()->first();
        $this->assertEquals($folderVerif->getPath() . $folderVerif->getName(), $children->getPath());
    }

    /**
     * Test méthode getNbInTrash()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetNbInTrash(): void
    {
        $folder = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => true]);
        $folder2 = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => false]);
        $this->createMedia($folder, customData: ['disabled' => false, 'trash' => true]);
        $this->createMedia($folder2, customData: ['disabled' => false, 'trash' => true]);

        $result = $this->mediaService->getNbInTrash();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('medias', $result);
        $this->assertEquals(2, $result['medias']);
        $this->assertArrayHasKey('folders', $result);
        $this->assertEquals(1, $result['folders']);
    }

    /**
     * Test méthode getAllMediaAndMediaFolderInTrash()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllMediaAndMediaFolderInTrash(): void
    {
        $folder = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => true]);
        $folder2 = $this->createMediaFolder(customData: ['disabled' => false, 'trash' => false]);
        $this->createMedia($folder, customData: ['disabled' => false, 'trash' => true]);
        $this->createMedia($folder2, customData: ['disabled' => false, 'trash' => true]);
        $this->createMedia($folder2, customData: ['disabled' => false, 'trash' => false]);

        $result = $this->mediaService->getAllMediaAndMediaFolderInTrash();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertCount(3, $result);

        $nbFolder = 0;
        $nbMedia = 0;
        foreach ($result as $data) {
            if ($data['type'] == 'media') {
                $this->assertArrayHasKey('type', $data);
                $this->assertArrayHasKey('id', $data);
                $this->assertArrayHasKey('name', $data);
                $this->assertArrayHasKey('description', $data);
                $this->assertArrayHasKey('size', $data);
                $this->assertArrayHasKey('webPath', $data);
                $this->assertArrayHasKey('thumbnail', $data);
                $this->assertArrayHasKey('created_at', $data);
                $nbMedia++;
            }

            if ($data['type'] == 'folder') {
                $this->assertArrayHasKey('type', $data);
                $this->assertArrayHasKey('id', $data);
                $this->assertArrayHasKey('name', $data);
                $this->assertArrayHasKey('created_at', $data);
                $nbFolder++;
            }
        }
        $this->assertEquals(2, $nbMedia);
        $this->assertEquals(1, $nbFolder);
    }

    /**
     * Test méthode updateTrash()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateTrash(): void
    {
        $mediaFolder = $this->createMediaFolder(customData: ['trash' => false]);
        $media = $this->createMedia($mediaFolder, customData: ['name' => self::IMG_UNIT_TEST, 'trash' => true]);
        $this->em->clear();

        $this->mediaService->updateTrash('media', $media->getId(), false);
        /** @var Media $verif */
        $verif = $this->mediaService->findOneById(Media::class, $media->getId());
        $this->assertEquals(!$media->isTrash(), $verif->isTrash());

        $this->mediaService->updateTrash('folder', $mediaFolder->getId(), true);
        /** @var MediaFolder $folderVerif */
        $verif = $this->mediaService->findOneById(MediaFolder::class, $mediaFolder->getId());
        $this->assertEquals(!$mediaFolder->isTrash(), $verif->isTrash());
    }

    /**
     * Test méthode confirmTrash()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testConfirmTrash(): void
    {
        $this->mediaService->resetAllMedia();
        $mediaFolder = $this->createMediaFolder(customData: ['trash' => false]);
        $media = $this->createMedia($mediaFolder, customData: ['name' => self::IMG_UNIT_TEST, 'trash' => true]);
        $this->mediaService->moveMediaFixture(self::IMG_UNIT_TEST, $media);

        $idMedia = $media->getId();
        $idFolder = $mediaFolder->getId();

        $this->mediaService->confirmTrash('folder', $mediaFolder->getId());
        $verif = $this->mediaService->findOneById(MediaFolder::class, $idFolder);
        $this->assertNull($verif);

        $verif = $this->mediaService->findOneById(Media::class, $idMedia);
        $this->assertNull($verif);

        $this->assertFalse(
            $this->fileSystem->exists(
                $this->mediaService->getRootPathMedia() . $mediaFolder->getPath() . $mediaFolder->getName(),
            ),
        );

        $mediaFolder = $this->createMediaFolder(customData: ['trash' => false]);
        $media = $this->createMedia($mediaFolder, customData: ['name' => self::IMG_UNIT_TEST, 'trash' => true]);
        $this->mediaService->moveMediaFixture(self::IMG_UNIT_TEST, $media);

        $idMedia = $media->getId();
        $this->mediaService->confirmTrash('media', $media->getId());
        $verif = $this->mediaService->findOneById(Media::class, $idMedia);
        $this->assertNull($verif);
        $this->assertFalse($this->fileSystem->exists($this->mediaService->getRootPathMedia() . $media->getPath()));
    }
}
