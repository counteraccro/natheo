<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests du service GitService
 */
namespace App\Tests\Service\Admin\Dev;

use App\Service\Admin\Dev\GitService;
use App\Tests\AppWebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class GitServiceTest extends AppWebTestCase
{
    private const string HASH = '0123456789abcdef0123456789abcdef01234567';

    private GitService $gitService;

    private string $gitPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->gitService = $this->container->get(GitService::class);
        $this->gitPath = sys_get_temp_dir() . '/natheo-git-' . uniqid();
    }

    public function tearDown(): void
    {
        (new Filesystem())->remove($this->gitPath);
        parent::tearDown();
    }

    /**
     * Test méthode getInfoGitFromPath() sur une branche
     * @return void
     */
    public function testGetInfoGitFromPathBranch(): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->gitPath . '/HEAD', "ref: refs/heads/feature/N-1-test\n");
        $filesystem->dumpFile($this->gitPath . '/refs/heads/feature/N-1-test', self::HASH . "\n");

        $result = $this->gitService->getInfoGitFromPath($this->gitPath);

        $this->assertEquals('feature/N-1-test', $result[GitService::KEY_BRANCHE]);
        $this->assertEquals(self::HASH, $result[GitService::KEY_HASH]);
        $this->assertNotEmpty($result[GitService::KEY_LAST_COMMIT]);
        $this->assertNotEmpty($result[GitService::KEY_LAST_COMMIT_SHORT]);
    }

    /**
     * Test méthode getInfoGitFromPath() avec un HEAD détaché (checkout d'un tag ou d'une PR en CI)
     * @return void
     */
    public function testGetInfoGitFromPathDetachedHead(): void
    {
        (new Filesystem())->dumpFile($this->gitPath . '/HEAD', self::HASH . "\n");

        $result = $this->gitService->getInfoGitFromPath($this->gitPath);

        $this->assertEquals('', $result[GitService::KEY_BRANCHE]);
        $this->assertEquals(self::HASH, $result[GitService::KEY_HASH]);
        $this->assertNotEmpty($result[GitService::KEY_LAST_COMMIT]);
    }

    /**
     * Test méthode getInfoGitFromPath() sans dépôt git ni référence de branche
     * @return void
     */
    public function testGetInfoGitFromPathMissing(): void
    {
        $result = $this->gitService->getInfoGitFromPath($this->gitPath);
        $this->assertEquals('', $result[GitService::KEY_HASH]);
        $this->assertEquals('', $result[GitService::KEY_LAST_COMMIT]);

        (new Filesystem())->dumpFile($this->gitPath . '/HEAD', "ref: refs/heads/master\n");
        $result = $this->gitService->getInfoGitFromPath($this->gitPath);
        $this->assertEquals('master', $result[GitService::KEY_BRANCHE]);
        $this->assertEquals('', $result[GitService::KEY_HASH]);
        $this->assertEquals('', $result[GitService::KEY_LAST_COMMIT]);
    }
}
