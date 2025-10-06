<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test DatabaseManagerController
 */

namespace App\Tests\Controller\Admin\Tools;

use App\Tests\AppWebTestCase;
use App\Utils\Tools\DatabaseManager\DatabaseManagerConst;
use Symfony\Component\Filesystem\Filesystem;

class DatabaseManagerControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_database_manager_index');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_database_manager_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('database_manager.index.page_title_h1', domain: 'database_manager'),
        );
    }

    /**
     * Test méthode schemaDatabase()
     * @return void
     */
    public function testSchemaDatabase(): void
    {
        $this->checkNoAccess('admin_database_manager_load_schema_database');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->client->request('GET', $this->router->generate('admin_database_manager_load_schema_database'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
    }

    /**
     * Test méthode schemaTable()
     * @return void
     */
    public function testSchemaTable(): void
    {
        $this->checkNoAccess('admin_database_manager_load_schema_table');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->client->request(
            'GET',
            $this->router->generate('admin_database_manager_load_schema_table', ['table' => 'user']),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
    }

    /**
     * Test méthode listeTablesDatabase()
     * @return void
     */
    public function testListeTablesDatabase(): void
    {
        $this->checkNoAccess('admin_database_manager_load_tables_database');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->client->request('GET', $this->router->generate('admin_database_manager_load_tables_database'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('tables', $content);
    }

    /**
     * test méthode saveBdd()
     * @return void
     */
    public function testSaveBdd(): void
    {
        $data = [
            'options' => [
                'all' => false,
                'tables' => ['api_token'],
                'data' => 'table',
            ],
        ];

        $this->checkNoAccess('admin_database_manager_save_database', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->client->request(
            'POST',
            $this->router->generate('admin_database_manager_save_database'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertTrue($content['success']);
    }

    /**
     * Test méthode getAllFileDump()
     * @return void
     */
    public function testGetAllFileDump(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile(
            self::$kernel->getProjectDir() . DatabaseManagerConst::ROOT_FOLDER_NAME . 'demo.sql',
            'dump',
        );

        $this->checkNoAccess('admin_database_manager_all_dump_file');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->client->request('GET', $this->router->generate('admin_database_manager_all_dump_file'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('result', $content);

        $fileSystem->remove(self::$kernel->getProjectDir() . DatabaseManagerConst::ROOT_FOLDER_NAME . 'demo.sql');
    }
}
