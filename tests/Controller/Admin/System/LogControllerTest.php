<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test logController
 */
namespace App\Tests\Controller\Admin\System;

use App\Service\LoggerService;
use App\Tests\AppWebTestCase;

class LogControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex() :void
    {
        $this->checkNoAccess('admin_log_index');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_log_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('log.page_title_h1', domain: 'log'));
    }

    /**
     * Test méthode dataSelect()
     * @return void
     */
    public function testDataSelect() :void
    {
        $this->checkNoAccess('admin_log_ajax_data_select_log');
        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_log_ajax_data_select_log'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('files', $content);
        $this->assertNotEmpty($content['files']);
        $this->assertArrayHasKey('trans', $content);
        $this->assertNotEmpty($content['trans']);
    }

    /**
     * Test méthode loadLogFile()
     * @return void
     */
    public function testLoadLogFile() :void
    {
        $logFile = 'auth-' . date('Y-m-d') . '.log';
        $loggerService = $this->container->get(LoggerService::class);

        $userSuperAdm = $this->createUserSuperAdmin();
        $loggerService->logAuthAdmin($userSuperAdm->getLogin(), 'ip-test', true);

        $this->checkNoAccess('admin_log_ajax_load_log_file', ['file' => $logFile]);

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_log_ajax_load_log_file', ['file' => $logFile, 'page' => 1, 'limit' => 1]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('grid', $content);
        $this->assertArrayHasKey('data', $content['grid']);
        $this->assertCount(1, $content['grid']['data']);
    }

    /**
     * Teste méthode deleteFile()
     * @return void
     */
    public function testDeleteFile() :void
    {
        $this->checkNoAccess('admin_log_ajax_delete_file', ['file' => 'auth-' . date('Y-m-d') . '.log'], 'DELETE');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');

        $logFile = 'auth-' . date('Y-m-d') . '.log';
        $loggerService = $this->container->get(LoggerService::class);
        $loggerService->logAuthAdmin($userSuperAdm->getLogin(), 'ip-test', true);

        $this->client->request('DELETE', $this->router->generate('admin_log_ajax_delete_file', ['file' => $logFile]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertNotEmpty($content['msg']);
    }

    /**
     * Test méthode downloadFile()
     * @return void
     */
    public function testDownloadFile() :void
    {
        $logFile = 'auth-' . date('Y-m-d') . '.log';
        $loggerService = $this->container->get(LoggerService::class);

        $this->checkNoAccess('admin_log_download_log', ['file' => $logFile]);
        $userSuperAdm = $this->createUserSuperAdmin();
        $loggerService->logAuthAdmin($userSuperAdm->getLogin(), 'ip-test', true);

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_log_download_log', ['file' => $logFile]));
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Disposition', 'attachment; filename=' . $logFile);
    }
}