<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test controller sqlManager
 */

namespace App\Tests\Controller\Admin\Tools;

use App\Entity\Admin\Tools\SqlManager;
use App\Repository\Admin\Utils\SqlManagerRepository;
use App\Tests\AppWebTestCase;

class SqlManagerControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_sql_manager_index');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_sql_manager_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('sql_manager.index.page_title_h1', domain: 'sql_manager'));
    }

    /**
     * Test méthode loadGridData()
     * @return void
     */
    public function testLoadGridData(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $sqlManager = $this->createSqlManager();
        }

        $this->checkNoAccess('admin_sql_manager_load_grid_data', ['page' => 1, 'limit' => 5]);

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_sql_manager_load_grid_data', ['page' => 1, 'limit' => 5]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(10, $content['nb']);
        $this->assertCount(5, $content['data']);

        $this->client->request('GET', $this->router->generate('admin_sql_manager_load_grid_data', ['page' => 1, 'limit' => 5, 'search' => $sqlManager->getName()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(1, $content['nb']);
        $this->assertCount(1, $content['data']);
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $sqlManager = $this->createSqlManager();

        $this->checkNoAccess('admin_sql_manager_disabled', ['id' => $sqlManager->getId()], 'PUT');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_sql_manager_disabled', ['id' => $sqlManager->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var SqlManagerRepository $repo */
        $repo = $this->em->getRepository(SqlManager::class);
        $verif = $repo->findOneBy(['id' => $sqlManager->getId()]);
        $this->assertEquals($verif->isDisabled(), !$sqlManager->isDisabled());
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        $sqlManager = $this->createSqlManager();

        $this->checkNoAccess('admin_sql_manager_delete', ['id' => $sqlManager->getId()], 'DELETE');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('DELETE', $this->router->generate('admin_sql_manager_delete', ['id' => $sqlManager->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var SqlManagerRepository $repo */
        $repo = $this->em->getRepository(SqlManager::class);
        $verif = $repo->findOneBy(['id' => $sqlManager->getId()]);
        $this->assertNull($verif);
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd(): void
    {
        $this->checkNoAccess('admin_sql_manager_add');

        $sqlManager = $this->createSqlManager(customData: ['disabled' => false]);

        $user = $this->createUserSuperAdmin();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_sql_manager_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('sql_manager.add.page_title_h1', domain: 'sql_manager'));

        $this->client->request('GET', $this->router->generate('admin_sql_manager_update', ['id' => $sqlManager->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('sql_manager.update.page_title_h1', domain: 'sql_manager'));

        $this->client->request('GET', $this->router->generate('admin_sql_manager_execute', ['id' => $sqlManager->getId(), 'isExecute' => true]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('sql_manager.execute.page_title_h1', domain: 'sql_manager'));

        $sqlManager = $this->createSqlManager(customData: ['disabled' => true]);
        $this->client->request('GET', $this->router->generate('admin_sql_manager_execute', ['id' => $sqlManager->getId(), 'isExecute' => true]));
        $this->assertResponseRedirects();
    }

    /**
     * Test méthode loadData()
     * @return void
     */
    public function testLoadData(): void
    {
        $this->checkNoAccess('admin_sql_manager_load_data');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_sql_manager_load_data'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('sqlManager', $content);
        $this->assertArrayHasKey('id', $content['sqlManager']);
        $this->assertArrayHasKey('name', $content['sqlManager']);
        $this->assertArrayHasKey('query', $content['sqlManager']);
        $this->assertArrayHasKey('disabled', $content['sqlManager']);
        $this->assertArrayHasKey('createdAt', $content['sqlManager']);
        $this->assertArrayHasKey('updateAt', $content['sqlManager']);

        $sqlManager = $this->createSqlManager();
        $this->client->request('GET', $this->router->generate('admin_sql_manager_load_data', ['id' => $sqlManager->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('sqlManager', $content);
        $this->assertArrayHasKey('id', $content['sqlManager']);
        $this->assertArrayHasKey('name', $content['sqlManager']);
        $this->assertArrayHasKey('query', $content['sqlManager']);
        $this->assertArrayHasKey('disabled', $content['sqlManager']);
        $this->assertArrayHasKey('createdAt', $content['sqlManager']);
        $this->assertArrayHasKey('updateAt', $content['sqlManager']);
        $this->assertEquals($content['sqlManager']['id'], $sqlManager->getId());
        $this->assertEquals($content['sqlManager']['name'], $sqlManager->getName());
        $this->assertEquals($content['sqlManager']['query'], $sqlManager->getQuery());
        $this->assertEquals($content['sqlManager']['disabled'], $sqlManager->isDisabled());
    }

    /**
     * Test méthode loadDataDataBase()
     * @return void
     */
    public function testLoadDataDataBase(): void
    {
        $this->checkNoAccess('admin_sql_manager_load_data_database');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_sql_manager_load_data_database'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertNotEmpty($content);
    }

    /**
     * Test méthode executeSQL()
     * @return void
     */
    public function testExecuteSQL(): void
    {
        $this->createSqlManager();

        $this->checkNoAccess('admin_sql_manager_execute_sql', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $data = ['query' => 'SELECT id, name from sql_manager'];
        $this->client->request('POST', $this->router->generate('admin_sql_manager_execute_sql'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey("result", $content['data']);
        $this->assertArrayHasKey("id", $content['data']['result'][0]);
        $this->assertArrayHasKey("name", $content['data']['result'][0]);
        $this->assertArrayHasKey("header", $content['data']);
        $this->assertArrayHasKey("error", $content['data']);
        $this->assertEmpty($content['data']['error']);

        $data = ['query' => 'SELECT toto from sql_manager'];
        $this->client->request('POST', $this->router->generate('admin_sql_manager_execute_sql'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey("error", $content['data']);
        $this->assertNotEmpty($content['data']['error']);

    }

    /**
     * test méthode save()
     * @return void
     */
    public function testSave(): void
    {
        $data = [
            "id" => null,
            "name" => 'test',
            "query" => 'SELECT * from test',
            "disabled" => false,
        ];

        $this->checkNoAccess('admin_sql_manager_save', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('POST', $this->router->generate('admin_sql_manager_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        /** @var SqlManagerRepository $repo */
        $repo = $this->em->getRepository(SqlManager::class);
        $verif = $repo->findOneBy(['name' => 'test']);

        $this->assertInstanceOf(SqlManager::class, $verif);
        $this->assertEquals('SELECT * from test', $verif->getQuery());

        $data = [
            "id" => $verif->getId(),
            "name" => 'test-edit',
            "query" => 'SELECT * from test',
            "disabled" => false,
        ];

        $this->checkNoAccess('admin_sql_manager_save', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('POST', $this->router->generate('admin_sql_manager_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        /** @var SqlManagerRepository $repo */
        $repo = $this->em->getRepository(SqlManager::class);
        $this->em->clear();
        $verif2 = $repo->findOneBy(['id' => $verif->getId()]);

        $this->assertInstanceOf(SqlManager::class, $verif2);
        $this->assertEquals('test-edit', $verif2->getName());

        $data = [
            "id" => null,
            "name" => 'test',
            "query" => 'DELETE from test where id=1',
            "disabled" => false,
        ];

        $this->checkNoAccess('admin_sql_manager_save', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('POST', $this->router->generate('admin_sql_manager_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);

    }

    /**
     * Test méthode saveGenericQuery()
     * @return void
     */
    public function testSaveGenericQuery(): void {
        $this->checkNoAccess('admin_sql_manager_save_generic_query', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $data = ['query' => 'SELECT * from test'];
        $this->client->request('POST', $this->router->generate('admin_sql_manager_save_generic_query'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var SqlManagerRepository $repo */
        $repo = $this->em->getRepository(SqlManager::class);
        $verif = $repo->findOneBy(['query' => $data['query']]);
        $this->assertInstanceOf(SqlManager::class, $verif);
        $this->assertEquals($data['query'], $verif->getQuery());
    }
}