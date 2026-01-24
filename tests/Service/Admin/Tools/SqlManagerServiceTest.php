<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test SqlManagerServiceTest
 */

namespace App\Tests\Service\Admin\Tools;

use App\Service\Admin\Tools\SqlManagerService;
use App\Tests\AppWebTestCase;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class SqlManagerServiceTest extends AppWebTestCase
{
    /**
     * @var mixed|object|Container|SqlManagerService
     */
    private SqlManagerService $sqlManagerService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->sqlManagerService = $this->container->get(SqlManagerService::class);
    }

    /**
     * Test de la méthode getAllFormatToGrid()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGrid(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $sqlManager = $this->createSqlManager();
        }

        $result = $this->sqlManagerService->getAllFormatToGrid(1, 5, []);

        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(10, $result['nb']);
        $this->assertCount(5, $result['data']);

        $result = $this->sqlManagerService->getAllFormatToGrid(1, 10, ['search' => $sqlManager->getName()]);
        $this->assertCount(1, $result['data']);
    }

    /**
     * Test méthode getAllPaginate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $sqlManager = $this->createSqlManager();
        }

        $result = $this->sqlManagerService->getAllPaginate(1, 7, []);
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(7, $result->getIterator()->count());
        $this->assertEquals(10, $result->count());

        $result = $this->sqlManagerService->getAllPaginate(1, 7, ['search' => $sqlManager->getName()]);
        $this->assertEquals(1, $result->getIterator()->count());
    }

    /**
     * test méthode isOnlySelectQuery()
     * @return void
     */
    public function testIsOnlySelectQuery(): void
    {
        $query = 'SELECT * from table test';
        $this->assertTrue($this->sqlManagerService->isOnlySelectQuery($query));

        $query = 'select * from table test';
        $this->assertTrue($this->sqlManagerService->isOnlySelectQuery($query));

        $query = 'truncate table table_test';
        $this->assertFalse($this->sqlManagerService->isOnlySelectQuery($query));

        $query = 'drop table table_test';
        $this->assertFalse($this->sqlManagerService->isOnlySelectQuery($query));

        $query = 'insert into table_test (id, name) values (1, \'name\')';
        $this->assertFalse($this->sqlManagerService->isOnlySelectQuery($query));

        $query = 'update table_test set = 10 where id = 1';
        $this->assertFalse($this->sqlManagerService->isOnlySelectQuery($query));

        $query = 'delete from table_test where id = 1';
        $this->assertFalse($this->sqlManagerService->isOnlySelectQuery($query));
    }
}
