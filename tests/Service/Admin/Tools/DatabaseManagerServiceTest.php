<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test DatabaseManagerService
 */

namespace App\Tests\Service\Admin\Tools;

use App\Service\Admin\Tools\DatabaseManagerService;
use App\Tests\AppWebTestCase;
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

    public function tearDown(): void
    {
        // Dossier propre à l'environnement de test, il peut être vidé sans risque
        (new Filesystem())->remove($this->getDumpDirectory());
        parent::tearDown();
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedStringException
     */
    public function testGetAllDump(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->getDumpDirectory() . 'demo.sql', 'dump');
        $result = $this->databaseManagerService->getAllDump();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('url', $result[0]);
        $fileSystem->remove($this->getDumpDirectory() . 'demo.sql');
    }

    /**
     * Test de la méthode deleteDumpFile())
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedStringException
     */
    public function testDeleteDumpFile(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->getDumpDirectory() . 'demo.sql', 'dump');
        $result = $this->databaseManagerService->deleteDumpFile('demo.sql');
        $this->assertEquals('', $result);

        $result = $this->databaseManagerService->getAllDump();
        $this->assertEmpty($result);

        foreach (['..', '.', '', 'demo.sql', '../../public/index.php'] as $filename) {
            $this->assertNotEquals('', $this->databaseManagerService->deleteDumpFile($filename));
        }
        $this->assertDirectoryExists($this->getDumpDirectory());
        $this->assertFileExists(self::$kernel->getProjectDir() . '/public/index.php');
    }

    /**
     * Test de la méthode getSchemaTableByTable() avec une table inconnue
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Doctrine\DBAL\Exception
     */
    public function testGetSchemaTableByTableUnknown(): void
    {
        $result = $this->databaseManagerService->getSchemaTableByTable("user'; DROP TABLE user; --");
        $this->assertEmpty($result['result']);
        $this->assertNotEmpty($result['error']);
        $this->assertEquals('', $result['table']);
    }

    /**
     * Test de la méthode getDumpFilePath()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetDumpFilePath(): void
    {
        $path = $this->getDumpDirectory() . 'demo.sql';
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($path, 'dump');

        $this->assertEquals($path, $this->databaseManagerService->getDumpFilePath('demo.sql'));
        $this->assertNull($this->databaseManagerService->getDumpFilePath('unknown.sql'));
        $this->assertNull($this->databaseManagerService->getDumpFilePath('../dump/demo.sql'));
        $this->assertNull($this->databaseManagerService->getDumpFilePath('demo'));
        $this->assertTrue($this->databaseManagerService->isDumpExist('demo'));
        $this->assertFalse($this->databaseManagerService->isDumpExist('unknown'));

        $fileSystem->remove($path);
    }

    /**
     * Retourne le dossier de stockage des dumps
     * @return string
     */
    private function getDumpDirectory(): string
    {
        return static::getContainer()->getParameter('app.dump_directory');
    }
}
