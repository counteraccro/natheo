<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test DatabaseManagerService
 */

namespace App\Tests\Service\Admin\Tools;

use App\Service\Admin\Tools\DatabaseManagerService;
use App\Tests\AppWebTestCase;
use App\Utils\Tools\DatabaseManager\DatabaseManagerConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class DatabaseManagerServiceTest extends AppWebTestCase
{
    /**
     * @var DatabaseManagerService
     */
    private DatabaseManagerService $databaseManagerService;

    public function setUp(): void
    {
        parent::setUp();
        $this->databaseManagerService = $this->container->get(DatabaseManagerService::class);
    }

    /**
     * test méthode getAllInformationSchemaDatabase()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllInformationSchemaDatabase(): void
    {
        $result = $this->databaseManagerService->getAllInformationSchemaDatabase();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertNotEmpty($result['result']);
        $this->assertArrayHasKey('header', $result);
        $this->assertNotEmpty($result['header']);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('stat', $result);
        $this->assertNotEmpty($result['stat']);
    }

    /**
     * test méthode getSchemaTableByTable()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetSchemaTableByTable(): void
    {
        $result = $this->databaseManagerService->getSchemaTableByTable('user');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertNotEmpty($result['result']);
        $this->assertArrayHasKey('header', $result);
        $this->assertNotEmpty($result['header']);
        $this->assertArrayHasKey('table', $result);
        $this->assertNotEmpty($result['table']);
    }

    /**
     * test méthode getAllDump()
     * @return void
     */
    public function testGetAllDump(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile(
            self::$kernel->getProjectDir() . DatabaseManagerConst::ROOT_FOLDER_NAME . 'demo.sql',
            'dump',
        );
        $result = $this->databaseManagerService->getAllDump();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('url', $result[0]);
        $fileSystem->remove(self::$kernel->getProjectDir() . DatabaseManagerConst::ROOT_FOLDER_NAME . 'demo.sql');
    }
}
