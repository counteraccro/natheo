<?php

declare(strict_types=1);
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
        $this->client->request(
            'POST',
            $this->router->generate('admin_search_index'),
            content: json_encode(['global-search-input' => '']),
        );
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('global_search.index.page_title_h1', domain: 'global_search'),
        );
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
        $this->client->request(
            'GET',
            $this->router->generate('admin_search_global', [
                'entity' => 'user',
                'page' => 1,
                'limit' => 10,
                'search' => $contributeur->getEmail(),
            ]),
        );
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

        // Un "/" ou des caractères de regex dans le critère ne doivent pas casser la recherche
        $search = 'a/b(c';
        $page = $this->createPageAllDataDefault();
        $page->getPageTranslationByLocale('fr')->setTitre('Titre ' . $search);
        $this->em->flush();
        foreach (['page', 'menu', 'faq', 'tag', 'user'] as $entity) {
            $this->client->request(
                'GET',
                $this->router->generate('admin_search_global', [
                    'entity' => $entity,
                    'page' => 1,
                    'limit' => 10,
                    'search' => $search,
                ]),
            );
            $this->assertResponseIsSuccessful();
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals($search, $content['recherche page']);
            if ($entity === 'page') {
                $this->assertEquals(1, $content['result']['total']);
            }
        }
    }
}
