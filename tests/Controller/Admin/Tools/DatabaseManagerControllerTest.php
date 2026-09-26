<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test DatabaseManagerController
 */

namespace App\Tests\Controller\Admin\Tools;

use App\Tests\AppWebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

class DatabaseManagerControllerTest extends AppWebTestCase
{
    public function tearDown(): void
    {
        // Dossier propre à l'environnement de test, il peut être vidé sans risque
        (new Filesystem())->remove($this->getDumpDirectory());
        parent::tearDown();
    }

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
        $this->assertEquals('user', $content['result']['table']);
        $this->assertNotEmpty($content['result']['result']);

        $this->client->request(
            'GET',
            $this->router->generate('admin_database_manager_load_schema_table', ['table' => "user' OR '1'='1"]),
        );
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEmpty($content['result']['result']);
        $this->assertNotEmpty($content['result']['error']);
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
        $token = $this->getCsrfTokens()['save_database'];

        $this->client->request(
            'POST',
            $this->router->generate('admin_database_manager_save_database'),
            server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide'],
            content: json_encode($data),
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertFalse(json_decode($this->client->getResponse()->getContent(), true)['success']);

        $this->client->request(
            'POST',
            $this->router->generate('admin_database_manager_save_database'),
            server: ['HTTP_X-CSRF-TOKEN' => $token],
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
     * test méthode saveBdd() avec des données invalides
     * @return void
     */
    public function testSaveBddInvalid(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->getDumpDirectory() . 'exist.sql', 'dump');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $server = ['HTTP_X-CSRF-TOKEN' => $this->getCsrfTokens()['save_database']];

        $cases = [
            ['all' => true, 'tables' => [], 'data' => 'unknown'],
            ['all' => false, 'tables' => [], 'data' => 'table'],
            ['all' => true, 'tables' => 'user', 'data' => 'table'],
            ['filename' => '../../public/evil', 'all' => true, 'tables' => [], 'data' => 'table'],
            ['filename' => 'exist', 'all' => true, 'tables' => [], 'data' => 'table'],
        ];

        foreach ($cases as $options) {
            $this->client->request(
                'POST',
                $this->router->generate('admin_database_manager_save_database'),
                server: $server,
                content: json_encode(['options' => $options]),
            );
            $this->assertResponseIsSuccessful();
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertFalse($content['success']);
            $this->assertNotEmpty($content['msg']);
        }

        $this->client->request(
            'POST',
            $this->router->generate('admin_database_manager_save_database'),
            server: $server,
            content: 'x',
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);

        $fileSystem->remove($this->getDumpDirectory() . 'exist.sql');
    }

    /**
     * Test méthode getAllFileDump()
     * @return void
     */
    public function testGetAllFileDump(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->getDumpDirectory() . 'demo.sql', 'dump');

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

        $fileSystem->remove($this->getDumpDirectory() . 'demo.sql');
    }

    /**
     * Test de la méthode deleteDumpFile
     * @return void
     */
    public function testDeleteDumpFile(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->getDumpDirectory() . 'demo.sql', 'dump');

        $this->checkNoAccess('admin_database_manager_delete_dump_file', methode: 'DELETE');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $server = ['HTTP_X-CSRF-TOKEN' => $this->getCsrfTokens()['delete_dump_file']];

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_database_manager_delete_dump_file', ['filename' => 'demo.sql']),
            server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide'],
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertFileExists($this->getDumpDirectory() . 'demo.sql');

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_database_manager_delete_dump_file', ['filename' => 'demo.sql']),
            server: $server,
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertTrue($content['success']);
        $this->assertFileDoesNotExist($this->getDumpDirectory() . 'demo.sql');

        foreach (['..', '.', 'demo.sql', 'demo.php'] as $filename) {
            $this->client->request(
                'DELETE',
                $this->router->generate('admin_database_manager_delete_dump_file', ['filename' => $filename]),
                server: $server,
            );
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertFalse($content['success']);
        }
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_database_manager_delete_dump_file'),
            server: $server,
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);

        $this->assertDirectoryExists($this->getDumpDirectory());
        $this->assertFileExists(self::$kernel->getProjectDir() . '/public/index.php');
    }

    /**
     * Test de la méthode downloadDumpFile
     * @return void
     */
    public function testDownloadDumpFile(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->getDumpDirectory() . 'demo.sql', 'dump');

        $this->checkNoAccess('admin_database_manager_download_dump_file', ['filename' => 'demo.sql']);

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->client->request(
            'GET',
            $this->router->generate('admin_database_manager_download_dump_file', ['filename' => 'demo.sql']),
        );
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-disposition', 'attachment; filename=demo.sql');

        foreach (['unknown.sql', '..', 'demo.php'] as $filename) {
            $this->client->request(
                'GET',
                $this->router->generate('admin_database_manager_download_dump_file', ['filename' => $filename]),
            );
            $this->assertResponseStatusCodeSame(404);
        }

        $fileSystem->remove($this->getDumpDirectory() . 'demo.sql');
    }

    /**
     * Retourne les jetons CSRF transmis au composant Vue de la page index
     * @return array
     */
    private function getCsrfTokens(): array
    {
        $crawler = $this->client->request('GET', $this->router->generate('admin_database_manager_index'));
        $props = $crawler
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/Tools/DatabaseManager/DatabaseManager"]')
            ->attr('data-symfony--ux-vue--vue-props-value');

        return json_decode($props, true)['csrfTokens'];
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
