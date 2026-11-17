<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Teste ApiDocController
 */

namespace App\Tests\Controller\Admin\System;

use App\Tests\AppWebTestCase;

class ApiDocControllerTest extends AppWebTestCase
{
    /**
     * Documentation complète réservée au super administrateur, opérations d'administration comprises
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_api_doc_index');
        $this->checkNoAccess('admin_api_doc_index', user: $this->createUserAdmin());

        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_api_doc_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('api_token.doc.title', domain: 'api_token'));

        $data = json_decode($this->client->getCrawler()->filter('#swagger-data')->text(), true);
        $this->assertArrayHasKey('/api/v2/admin/probes', $data['spec']['paths']);
        $this->assertArrayHasKey('/api/v2/info', $data['spec']['paths']);
        $this->assertFalse($data['oauth']['enabled']);
    }
}
