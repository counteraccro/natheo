<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests du service InstallRequirementsChecker
 */
namespace App\Tests\Service\Installation;

use App\Service\Installation\InstallRequirementsChecker;
use App\Tests\AppWebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class InstallRequirementsCheckerTest extends AppWebTestCase
{
    private InstallRequirementsChecker $installRequirementsChecker;

    private Filesystem $filesystem;

    private string $basePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->installRequirementsChecker = $this->container->get(InstallRequirementsChecker::class);
        $this->filesystem = new Filesystem();
        $this->basePath = sys_get_temp_dir() . '/natheo-requirements-' . uniqid();
        $this->filesystem->mkdir($this->basePath);
    }

    public function tearDown(): void
    {
        $this->filesystem->chmod($this->basePath, 0755, 0000, true);
        $this->filesystem->remove($this->basePath);
        parent::tearDown();
    }

    /**
     * Test méthode isWritableDirectory() : un dossier absent est créé
     * @return void
     */
    public function testIsWritableDirectoryCreatesMissing(): void
    {
        $path = $this->basePath . '/var/sessions';

        $this->assertTrue($this->installRequirementsChecker->isWritableDirectory($path));
        $this->assertDirectoryExists($path);
        $this->assertCount(0, glob($path . '/.write_test_*'));
    }

    /**
     * Test méthode isWritableDirectory() : un dossier impossible à créer reste non inscriptible
     * @return void
     */
    public function testIsWritableDirectoryReadOnlyParent(): void
    {
        $this->filesystem->chmod($this->basePath, 0555);
        if (is_writable($this->basePath)) {
            $this->markTestSkipped('Droits non appliqués (exécution en root)');
        }

        $path = $this->basePath . '/thumbnails';
        $this->assertFalse($this->installRequirementsChecker->isWritableDirectory($path));
        $this->assertDirectoryDoesNotExist($path);
    }

    /**
     * Test méthode checkWritableDirectories() : tous les dossiers du projet sont inscriptibles
     * @return void
     */
    public function testCheckWritableDirectories(): void
    {
        $result = $this->installRequirementsChecker->checkWritableDirectories();
        $this->assertArrayHasKey('var/sessions', $result);
        $this->assertArrayHasKey('public/assets/thumbnails', $result);
        $this->assertNotContains(false, $result);
    }
}
