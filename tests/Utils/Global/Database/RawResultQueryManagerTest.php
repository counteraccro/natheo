<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test RawResultQueryManager
 */
namespace App\Tests\Utils\Global\Database;

use App\Tests\AppWebTestCase;
use App\Utils\Global\Database\DataBase;
use App\Utils\Global\Database\RawQueryManager;
use App\Utils\Global\Database\RawResultQueryManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class RawResultQueryManagerTest extends AppWebTestCase
{
    /**
     * @var DataBase
     */
    private Database $database;

    /**
     * @var RawQueryManager
     */
    private RawQueryManager $rawQueryManager;

    /**
     * @var RawResultQueryManager
     */
    private RawResultQueryManager $rawResultQueryManager;

    private TranslatorInterface $translate;

    public function setUp(): void
    {
        parent::setUp();
        $this->database = $this->container->get(Database::class);
        $this->rawQueryManager = new RawQueryManager();
        $this->rawResultQueryManager = new RawResultQueryManager();
        $this->translate = $this->container->get(TranslatorInterface::class);
    }

    /**
     * Test méthode getResultAllInformationSchema()
     * @return void
     */
    public function testGetResultAllInformationSchema() : void
    {
        $sql = $this->rawQueryManager->getQueryAllInformationSchema('natheo_test');
        $result = $this->database->executeRawQuery($sql);
        $result = $this->rawResultQueryManager->getResultAllInformationSchema($result, $this->translate);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertNotEmpty($result['result']);
        $this->assertArrayHasKey('schema', $result['result'][0]);
        $this->assertArrayHasKey('table_name', $result['result'][0]);
        $this->assertArrayHasKey('row', $result['result'][0]);
        $this->assertArrayHasKey('size', $result['result'][0]);

        $this->assertArrayHasKey('stat', $result);
        $this->assertNotEmpty($result['stat']);
        $this->assertArrayHasKey('nbElement', $result['stat']);
        $this->assertArrayHasKey('sizeBite', $result['stat']);
        $this->assertArrayHasKey('nbTable', $result['stat']);

        $this->assertArrayHasKey('header', $result);
        $this->assertNotEmpty($result['header']);
        $this->assertArrayHasKey('schema', $result['header']);
        $this->assertArrayHasKey('table_name', $result['header']);
        $this->assertArrayHasKey('row', $result['header']);
        $this->assertArrayHasKey('size', $result['header']);
    }

    /**
     * Test méthode getResultStructureTable()
     * @return void
     */
    public function testGetResultStructureTable() :void
    {
        $sql = $this->rawQueryManager->getQueryStructureTable('user');
        $result = $this->database->executeRawQuery($sql);
        $result = $this->rawResultQueryManager->getResultStructureTable($result, $this->translate);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertNotEmpty($result['result']);
        $this->assertArrayHasKey('column_name', $result['result'][0]);
        $this->assertArrayHasKey('data_type', $result['result'][0]);
        $this->assertArrayHasKey('character_maximum_length', $result['result'][0]);
        $this->assertArrayHasKey('is_nullable', $result['result'][0]);
        $this->assertArrayHasKey('column_default', $result['result'][0]);

        $this->assertArrayHasKey('header', $result);
        $this->assertNotEmpty($result['header']);
        $this->assertArrayHasKey('column_name', $result['header']);
        $this->assertArrayHasKey('data_type', $result['header']);
        $this->assertArrayHasKey('character_maximum_length', $result['header']);
        $this->assertArrayHasKey('is_nullable', $result['header']);
        $this->assertArrayHasKey('column_default', $result['header']);
    }

    /**
     * test méthode getResultExistTable()
     * @return void
     */
    public function testGetResultExistTable() :void
    {
        $sql = $this->rawQueryManager->getQueryExistTable('natheo_test', 'user');
        $result = $this->database->executeRawQuery($sql);
        $result = $this->rawResultQueryManager->getResultExistTable($result);
        $this->assertTrue($result);

        $sql = $this->rawQueryManager->getQueryExistTable('natheo_test', 'test-unit');
        $result = $this->database->executeRawQuery($sql);
        $result = $this->rawResultQueryManager->getResultExistTable($result);
        $this->assertFalse($result);
    }
}