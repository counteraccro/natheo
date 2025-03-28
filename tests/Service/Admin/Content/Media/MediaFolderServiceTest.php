<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test service
 */

namespace Service\Admin\Content\Media;

use App\Entity\Admin\Content\Media\MediaFolder;
use App\Service\Admin\Content\Media\MediaFolderService;
use App\Tests\AppWebTestCase;
use App\Utils\Utils;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class MediaFolderServiceTest extends AppWebTestCase
{
    /**
     * @var MediaFolderService
     */
    private MediaFolderService $mediaFolderService;

    /**
     * @var FileSystem
     */
    private Filesystem $fileSystem;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->mediaFolderService = $this->container->get(MediaFolderService::class);
        $this->fileSystem = new FileSystem();
    }

    /**
     * Test méthode resetAllMedia()
     * @return void
     */
    public function testResetAllMedia(): void
    {
        $this->mediaFolderService->resetAllMedia();

        $this->assertTrue($this->fileSystem->exists($this->mediaFolderService->getRootPathMedia()));
        $this->assertTrue($this->fileSystem->exists($this->mediaFolderService->getRootPathThumbnail()));
    }

    /**
     * Test méthode createFolder()
     * @return void
     */
    public function testCreateFolder(): void
    {
        $this->mediaFolderService->resetAllMedia();
        $mediaFolder = $this->createMediaFolder();
        $this->mediaFolderService->createFolder($mediaFolder);
        $this->assertTrue($this->fileSystem->exists($this->mediaFolderService->getRootPathMedia() . $mediaFolder->getPath()));

        $subMediaFolder = $this->createMediaFolder($mediaFolder);
        $this->mediaFolderService->createFolder($subMediaFolder);
        $this->assertTrue($this->fileSystem->exists($this->mediaFolderService->getRootPathMedia() . $subMediaFolder->getPath()));

        $subSubMediaFolder = $this->createMediaFolder($subMediaFolder);
        $this->mediaFolderService->createFolder($subSubMediaFolder);
        $this->assertTrue($this->fileSystem->exists($this->mediaFolderService->getRootPathMedia() . $subSubMediaFolder->getPath()));

        $subSubMediaFolder2 = $this->createMediaFolder($subMediaFolder);
        $this->mediaFolderService->createFolder($subSubMediaFolder2);
        $this->assertTrue($this->fileSystem->exists($this->mediaFolderService->getRootPathMedia() . $subSubMediaFolder2->getPath()));
    }

    /**
     * test méthode getPathFolder()
     * @return void
     */
    public function testGetPathFolder(): void
    {
        $mediaFolder = $this->createMediaFolder();
        $subMediaFolder = $this->createMediaFolder($mediaFolder);
        $this->createMediaFolder($subMediaFolder);

        $result = $this->mediaFolderService->getPathFolder($subMediaFolder, false);
        $verif = $this->mediaFolderService->getRootPathMedia() . $subMediaFolder->getPath() . DIRECTORY_SEPARATOR . $subMediaFolder->getName();
        $this->assertEquals($verif, $result);

        $result = $this->mediaFolderService->getPathFolder($subMediaFolder);
        $this->assertEquals($verif . DIRECTORY_SEPARATOR, $result);
    }

    /**
     * Test méthode getMediaFolderByMediaFolder()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetMediaFolderByMediaFolder(): void
    {
        $mediaFolder = $this->createMediaFolder();
        $subMediaFolder = $this->createMediaFolder($mediaFolder, ['disabled' => false, 'trash' => false]);
        $subMediaFolder2 = $this->createMediaFolder($mediaFolder, ['disabled' => false, 'trash' => false]);
        $subMediaFolder3 = $this->createMediaFolder($mediaFolder, ['disabled' => false, 'trash' => true]);

        $result = $this->mediaFolderService->getMediaFolderByMediaFolder($mediaFolder);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(MediaFolder::class, $result[0]);

        $result = $this->mediaFolderService->getMediaFolderByMediaFolder($subMediaFolder);
        $this->assertEmpty($result);

        $result = $this->mediaFolderService->getMediaFolderByMediaFolder($mediaFolder, true);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(MediaFolder::class, $result[0]);

        $result = $this->mediaFolderService->getMediaFolderByMediaFolder($subMediaFolder, true, true);
        $this->assertEmpty($result);
    }

    /**
     * Test méthode getMediaFolderInfo()
     * @return void
     */
    public function testGetMediaFolderInfo(): void
    {

        $this->mediaFolderService->resetAllMedia();
        $mediaFolder = $this->createMediaFolder();
        $this->mediaFolderService->createFolder($mediaFolder);
        $subMediaFolder = $this->createMediaFolder($mediaFolder);
        $this->mediaFolderService->createFolder($subMediaFolder);
        $subMediaFolder2 = $this->createMediaFolder($mediaFolder);
        $this->mediaFolderService->createFolder($subMediaFolder2);
        $subSubMediaFolder = $this->createMediaFolder($subMediaFolder);
        $this->mediaFolderService->createFolder($subSubMediaFolder);

        $result = $this->mediaFolderService->getMediaFolderInfo($subSubMediaFolder);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('root', $result);
        $this->assertCount(3, $result['root']);
        $this->assertArrayHasKey('size', $result);
        $this->assertEquals(Utils::getSizeName(0), $result['size']);
        $this->assertArrayHasKey('path', $result);
        $this->assertEquals($subSubMediaFolder->getPath() . DIRECTORY_SEPARATOR . $subSubMediaFolder->getName(), $result['path']);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($subSubMediaFolder->getId(), $result['id']);
    }

    /**
     * Test méthode getTreeFolder()
     * @return void
     */
    public function testGetTreeFolder(): void
    {
        $mediaFolder = $this->createMediaFolder();
        $subMediaFolder = $this->createMediaFolder($mediaFolder);
        $subMediaFolder2 = $this->createMediaFolder($mediaFolder);
        $subSubMediaFolder = $this->createMediaFolder($subMediaFolder);
        $result = $this->mediaFolderService->getTreeFolder($subSubMediaFolder);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertCount(3, $result);

        $result = $this->mediaFolderService->getTreeFolder($subMediaFolder);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);

        $result = $this->mediaFolderService->getTreeFolder($mediaFolder);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
    }

    /**
     * Test méthode getFolderSize()
     * @return void
     */
    public function testGetFolderSize(): void
    {
        $this->mediaFolderService->resetAllMedia();
        $mediaFolder = $this->createMediaFolder();
        $this->mediaFolderService->createFolder($mediaFolder);
        $result = $this->mediaFolderService->getFolderSize($mediaFolder);
        $this->assertEquals(0, $result);
    }

    /**
     * Test méthode createMediaFolder()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateMediaFolder(): void
    {
        $this->mediaFolderService->resetAllMedia();
        $this->mediaFolderService->createMediaFolder('unit-test');

        $result = $this->mediaFolderService->findOneBy(MediaFolder::class, 'name', 'unit-test');
        $this->assertNotNull($result);
        $this->assertInstanceOf(MediaFolder::class, $result);
        $exist = $this->fileSystem->exists($this->mediaFolderService->getRootPathMedia() . $result->getPath() . DIRECTORY_SEPARATOR . $result->getName());
        $this->assertTrue($exist);

        $this->mediaFolderService->createMediaFolder('sub-unit-test', $result);
        $result = $this->mediaFolderService->findOneBy(MediaFolder::class, 'name', 'sub-unit-test');
        $this->assertNotNull($result);
        $this->assertInstanceOf(MediaFolder::class, $result);
        $exist = $this->fileSystem->exists($this->mediaFolderService->getRootPathMedia() . $result->getPath() . DIRECTORY_SEPARATOR . $result->getName());
        $this->assertTrue($exist);
    }

    /**
     * test méthode updateMediaFolder()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateMediaFolder(): void
    {
        $this->mediaFolderService->resetAllMedia();
        $this->mediaFolderService->createMediaFolder('unit-test');

        $result = $this->mediaFolderService->findOneBy(MediaFolder::class, 'name', 'unit-test');
        $this->mediaFolderService->createMediaFolder('sub-unit-test', $result);
        $this->mediaFolderService->createMediaFolder('sub-unit-test-2', $result);


        $this->mediaFolderService->updateMediaFolder('edit-unit-test', $result);

        /** @var MediaFolder $result */
        $verif = $this->mediaFolderService->findOneBy(MediaFolder::class, 'name', 'edit-unit-test');
        $this->assertNotNull($verif);

        foreach($result->getChildren() as $child) {
            $this->assertStringContainsString('edit-unit-test', $child->getPath());
        }

        $exist = $this->fileSystem->exists($this->mediaFolderService->getRootPathMedia() . $result->getPath() . DIRECTORY_SEPARATOR . $result->getName());
        $this->assertTrue($exist);
    }

    /**
     * test méthode getInfoFolder()
     * @return void
     */
    public function testGetInfoFolder(): void
    {

    }
}