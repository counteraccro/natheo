<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test RawQueryManager
 */
namespace App\Tests\Utils\Global\Database;

use App\Tests\AppWebTestCase;
use App\Utils\Global\Database\DataBase;
use App\Utils\Global\Database\RawQueryManager;

class RawQueryManagerTest extends AppWebTestCase
{
    /**
     * @var DataBase
     */
    private Database $database;

    private RawQueryManager $rawQueryManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->database = $this->container->get(Database::class);
        $this->rawQueryManager = new RawQueryManager();
    }

    /**
     * Test méthode getQueryAllInformationSchema()
     * @return void
     */
    public function testGetQueryAllInformationSchema() :void
    {
        $sql = $this->rawQueryManager->getQueryAllInformationSchema('natheo_test');
        $this->assertNotEmpty($sql);
        $this->assertIsString($sql);
        $result = $this->database->executeRawQuery($sql);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['result']);
        $this->assertNotEmpty($result['header']);
        $this->assertEmpty($result['error']);
    }

    /**
     * Test méthode getQueryStructureTable()
     * @return void
     */
    public function testGetQueryStructureTable() :void
    {
        $sql = $this->rawQueryManager->getQueryStructureTable('user');
        $this->assertNotEmpty($sql);
        $this->assertIsString($sql);
        $result = $this->database->executeRawQuery($sql);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['result']);
        $this->assertNotEmpty($result['header']);
        $this->assertEmpty($result['error']);
    }

    /**
     * Test méthode getQueryExistTable()
     * @return void
     */
    public function testGetQueryExistTable() :void
    {
        $sql = $this->rawQueryManager->getQueryExistTable('natheo_test', 'user');
        $this->assertNotEmpty($sql);
        $this->assertIsString($sql);
        $result = $this->database->executeRawQuery($sql);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['result']);
        $this->assertNotEmpty($result['header']);
        $this->assertEmpty($result['error']);
    }

    /**
     * Test méthode getQueryAllDatabase()
     * @return void
     */
    public function testGetQueryAllDatabase() :void
    {
        $sql = $this->rawQueryManager->getQueryAllDatabase();
        $this->assertNotEmpty($sql);
        $this->assertIsString($sql);
        $result = $this->database->executeRawQuery($sql);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['result']);
        $this->assertNotEmpty($result['header']);
        $this->assertEmpty($result['error']);
    }

    /**
     * Test méthode getQueryPurgeNotification()
     * @return void
     */
    public function testGetQueryPurgeNotification() :void
    {
        $sql = $this->rawQueryManager->getQueryPurgeNotification();
        $this->assertNotEmpty($sql);
        $this->assertIsString($sql);
    }
}