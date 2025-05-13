<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Test controller globalSearch
 */

namespace App\Tests\Controller\Admin\Global;

use App\Tests\AppWebTestCase;

class GlobalSearchControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');
        $this->client->request('POST', $this->router->generate('admin_search_index'), content: json_encode(['global-search-input' => '']));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('global_search.index.page_title_h1', domain: 'global_search'));
    }

    /**
     * Test méthode search()
     * @return void
     */
    public function testSearch(): void
    {
        $this->createUser();
        $contributeur = $this->createUserContributeur();

        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_search_global', ['entity' => 'user', 'page' => 1, 'limit' => 10, 'search' => $contributeur->getEmail()]));
        $this->assertResponseIsSuccessful();
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('recherche page', $content);
        $this->assertArrayHasKey('result', $content);
        $this->assertArrayHasKey('elements', $content['result']);
        $this->assertCount(1, $content['result']['elements']);
        $this->assertArrayHasKey('id', $content['result']['elements'][0]);
        $this->assertEquals($contributeur->getId(), $content['result']['elements'][0]['id']);
        $this->assertArrayHasKey('total', $content['result']);
        $this->assertEquals(1, $content['result']['total']);
        $this->assertArrayHasKey('paginate', $content);
        $this->assertArrayHasKey('current', $content['paginate']);
        $this->assertEquals(1, $content['paginate']['current']);
        $this->assertArrayHasKey('limit', $content['paginate']);
        $this->assertEquals(10, $content['paginate']['limit']);

    }
}