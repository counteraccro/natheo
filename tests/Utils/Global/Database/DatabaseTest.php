<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test Database
 */
namespace App\Tests\Utils\Global\Database;

use App\Entity\Admin\System\User;
use App\Tests\AppWebTestCase;
use App\Utils\Global\Database\DataBase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DatabaseTest extends AppWebTestCase
{
    /**
     * @var DataBase
     */
    private Database $database;

    public function setUp(): void
    {
        parent::setUp();
        $this->database = $this->container->get(Database::class);
    }

    /**
     * Test méthode isConnected()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testIsConnected(): void
    {
        $result = $this->database->isConnected();
        $this->assertTrue($result);
    }

    /**
     * Test méthode isTableExiste()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testIsTableExiste(): void
    {
        $result = $this->database->isTableExiste('test-unit');
        $this->assertFalse($result);

        $result = $this->database->isTableExiste('user');
        $this->assertTrue($result);
    }

    /**
     * Test méthode isDataInTable()
     * @return void
     */
    public function testIsDataInTable(): void
    {
        $result = $this->database->isDataInTable(User::class);
        $this->assertFalse($result);

        $this->createUser();
        $result = $this->database->isDataInTable(User::class);
        $this->assertTrue($result);
    }

    /**
     * Test méthode isSchemaExist()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testIsSchemaExist(): void
    {
        $result = $this->database->isSchemaExist();
        $this->assertTrue($result);
    }

    /**
     * Test méthode getAllNameAndColumn()
     * @return void
     */
    public function testGetAllNameAndColumn(): void
    {
        $result = $this->database->getAllNameAndColumn();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertIsString($result[0]['name']);
        $this->assertArrayHasKey('columns', $result[0]);
        $this->assertIsArray($result[0]['columns']);
    }

    /**
     * Test méthode getNameAndColumByEntity()
     * @return void
     */
    public function testGetNameAndColumByEntity(): void
    {
        $result = $this->database->getNameAndColumByEntity('toto');
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        $result = $this->database->getNameAndColumByEntity(User::class);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertIsString($result['name']);
        $this->assertArrayHasKey('column', $result);
        $this->assertIsArray($result['column']);
    }

    /**
     * Test méthode executeRawQuery()
     * @return void
     */
    public function testExecuteRawQuery(): void
    {
        $user = $this->createUser();

        $query = 'SELECT id, email FROM user';
        $result = $this->database->executeRawQuery($query);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('result', $result);
        $row = $result['result'][0];
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('email', $row);
        $this->assertEquals($user->getId(), $row['id']);
        $this->assertEquals($user->getEmail(), $row['email']);

        $this->assertArrayHasKey('header', $result);
        $this->assertEquals('id', $result['header'][0]);
        $this->assertEquals('email', $result['header'][1]);

        $query = 'SELECT id, email2 FROM user';
        $result = $this->database->executeRawQuery($query);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
    }
}