<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test service
 */

namespace Service\Admin\Content\Media;

use App\Service\Admin\Content\Media\MediaFolderService;
use App\Tests\AppWebTestCase;
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
}