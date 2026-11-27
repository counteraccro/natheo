<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Teste ApiDocController
 */

namespace App\Tests\Controller\Admin\System;

use App\Service\Admin\Module\ModuleActivationService;
use App\Tests\Api\V2\Module\ProbeModule;
use App\Tests\AppWebTestCase;

class ApiDocControllerTest extends AppWebTestCase
{
    /**
     * Documentation avec les opérations d'administration, réservée aux administrateurs
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_api_doc_index');
        $this->checkNoAccess('admin_api_doc_index', user: $this->createUserContributeur());

        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_api_doc_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('api_token.doc.title', domain: 'api_token'));

        $crawler = $this->client->getCrawler();
        foreach (['swagger-ui-bundle.js', 'swagger-ui-standalone-preset.js', 'init-swagger-ui.js'] as $script) {
            $this->assertCount(1, $crawler->filter('head script[src*="' . $script . '"]'), $script);
        }

        $data = json_decode($crawler->filter('#swagger-data')->text(), true);
        $this->assertArrayHasKey('/api/v2/admin/probes', $data['spec']['paths']);
        $this->assertArrayHasKey('/api/v2/info', $data['spec']['paths']);
        $this->assertFalse($data['oauth']['enabled']);
    }

    /**
     * Les opérations désactivées et celles d'un module désactivé sont retirées de la documentation
     * @return void
     */
    public function testDisabledOperations(): void
    {
        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $activationService = $this->container->get(ModuleActivationService::class);

        $activationService->setOperationDisabled('admin_probe_get', true);
        $paths = $this->getSpecPaths();
        $this->assertArrayHasKey('/api/v2/admin/probes', $paths);
        $this->assertArrayHasKey('patch', $paths['/api/v2/admin/probes/{id}']);
        $this->assertArrayNotHasKey('get', $paths['/api/v2/admin/probes/{id}']);

        $activationService->setModuleDisabled(ProbeModule::ID, true);
        $paths = $this->getSpecPaths();
        $this->assertArrayNotHasKey('/api/v2/admin/probes', $paths);
        $this->assertArrayNotHasKey('/api/v2/admin/probes/{id}', $paths);
        $this->assertArrayHasKey('/api/v2/admin/modules', $paths);
    }

    /**
     * Chemins de la documentation du back-office
     * @return array<string, array<string, mixed>>
     */
    private function getSpecPaths(): array
    {
        $this->client->request('GET', $this->router->generate('admin_api_doc_index'));
        $this->assertResponseIsSuccessful();
        return json_decode($this->client->getCrawler()->filter('#swagger-data')->text(), true)['spec']['paths'];
    }
}
