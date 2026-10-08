<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * test logController
 */
namespace App\Tests\Controller\Admin\System;

use App\Service\LoggerService;
use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LogControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_log_index');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_log_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('log.page_title_h1', domain: 'log'));

        $props = $this->getVueProps();
        $this->assertNotEmpty($props['translate']);
        $this->assertNotEmpty($props['csrf_delete_file']);
        $this->assertIsInt($props['limit']);
    }

    /**
     * Test méthode dataSelect()
     * @return void
     */
    public function testDataSelect(): void
    {
        $this->checkNoAccess('admin_log_ajax_data_select_log');
        $userSuperAdm = $this->createUserSuperAdmin();
        $this->container->get(LoggerService::class)->logAuthAdmin($userSuperAdm->getLogin(), 'ip-test');

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_log_ajax_data_select_log'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('files', $content);
        $this->assertNotEmpty($content['files']);

        foreach (['now', 'yesterday', date('Y-m-d')] as $time) {
            $this->client->request('GET', $this->router->generate('admin_log_ajax_data_select_log', ['time' => $time]));
            $this->assertResponseIsSuccessful();
        }

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains($this->getAuthLogFile(), array_column($content['files'], 'path'));

        $this->client->request('GET', $this->router->generate('admin_log_ajax_data_select_log') . '/2026-13-45');
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);

        $this->client->request('GET', $this->router->generate('admin_log_ajax_data_select_log') . '/invalide');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Test méthode loadLogFile()
     * @return void
     */
    public function testLoadLogFile(): void
    {
        $loggerService = $this->container->get(LoggerService::class);

        $userSuperAdm = $this->createUserSuperAdmin();
        $loggerService->logAuthAdmin($userSuperAdm->getLogin(), 'ip-test', true);

        $logFile = $this->getAuthLogFile();
        $this->checkNoAccess('admin_log_ajax_load_log_file', ['file' => $logFile]);

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_log_ajax_load_log_file', ['file' => $logFile, 'page' => 1, 'limit' => 1]),
        );
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

        foreach (['toto.log', '*', '../../.env'] as $file) {
            $this->client->request(
                'GET',
                $this->router->generate('admin_log_ajax_load_log_file', ['file' => $file, 'page' => 1, 'limit' => 1]),
            );
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertFalse($content['success']);
            $this->assertNotEmpty($content['msg']);
        }

        $this->client->request('GET', $this->router->generate('admin_log_ajax_load_log_file') . '/0/10/' . $logFile);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Teste méthode deleteFile()
     * @return void
     */
    public function testDeleteFile(): void
    {
        $logFile = $this->getAuthLogFile();
        $this->checkNoAccess('admin_log_ajax_delete_file', ['file' => $logFile], 'DELETE');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $loggerService = $this->container->get(LoggerService::class);
        $loggerService->logAuthAdmin($userSuperAdm->getLogin(), 'ip-test', true);
        $this->client->request('GET', $this->router->generate('admin_log_index'));
        $server = ['HTTP_X-CSRF-TOKEN' => $this->getVueProps()['csrf_delete_file']];

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_log_ajax_delete_file', ['file' => $logFile]),
            server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide'],
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertNotNull($loggerService->getPathFile($logFile));

        foreach (['*', '*.log', 'toto.log'] as $file) {
            $this->client->request(
                'DELETE',
                $this->router->generate('admin_log_ajax_delete_file', ['file' => $file]),
                server: $server,
            );
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
        $this->assertNotNull($loggerService->getPathFile($logFile));

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_log_ajax_delete_file', ['file' => $logFile]),
            server: $server,
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertNotEmpty($content['msg']);
        $this->assertNull($loggerService->getPathFile($logFile));
    }

    /**
     * Test méthode downloadFile()
     * @return void
     */
    public function testDownloadFile(): void
    {
        $logFile = $this->getAuthLogFile();
        $loggerService = $this->container->get(LoggerService::class);

        $this->checkNoAccess('admin_log_download_log', ['file' => $logFile]);
        $userSuperAdm = $this->createUserSuperAdmin();
        $loggerService->logAuthAdmin($userSuperAdm->getLogin(), 'ip-test', true);

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_log_download_log', ['file' => $logFile]));
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Disposition', 'attachment; filename=' . basename($logFile));

        foreach (['toto.log', '*', '../../composer.json'] as $file) {
            $this->client->request('GET', $this->router->generate('admin_log_download_log', ['file' => $file]));
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Retourne les props transmises au composant Vue de la dernière page index chargée
     * @return array
     */
    private function getVueProps(): array
    {
        $props = $this->client
            ->getCrawler()
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/System/Log"]')
            ->attr('data-symfony--ux-vue--vue-props-value');

        return json_decode($props, true);
    }

    /**
     * Retourne le chemin relatif du log d'authentification du jour
     * @return string
     */
    private function getAuthLogFile(): string
    {
        return 'cms/' . self::$kernel->getEnvironment() . '/auth-' . date('Y-m-d') . '.log';
    }
}
