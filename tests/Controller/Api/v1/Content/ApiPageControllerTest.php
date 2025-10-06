<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Tests\Controller\Api\v1\Content;

use App\Tests\Controller\Api\AppApiTestCase;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageStatistiqueKey;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiPageControllerTest extends AppApiTestCase
{
    /**
     * Test de la méthode find()
     * @return void
     */
    public function testFind(): void
    {
        $page = $this->createPageAllDataDefault();
        $slug = $page->getPageTranslationByLocale('fr')->getUrl();
        $menu = $this->createMenuAllDataDefault();

        $page->addMenu($menu);
        $this->em->persist($page);
        $this->em->flush();

        $this->client->request(
            'GET',
            $this->router->generate('api_page_find', [
                'api_version' => self::API_VERSION,
                'slug' => $slug,
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->checkStructureApiRetour($content);
        $this->assertArrayHasKey('page', $content['data']);
        $this->assertArrayHasKey('tags', $content['data']['page']);
        $this->assertCount($page->getTags()->count(), $content['data']['page']['tags']);
        $this->assertArrayHasKey('statistiques', $content['data']['page']);
        $statistique = $page->getPageStatistiqueByKey(PageStatistiqueKey::KEY_PAGE_NB_READ);
        $this->assertEquals(
            $statistique->getValue() + 1,
            $content['data']['page']['statistiques'][PageStatistiqueKey::KEY_PAGE_NB_READ],
        );
        $this->assertArrayHasKey('contents', $content['data']['page']);
        $this->assertArrayHasKey('menus', $content['data']['page']);
        $this->assertArrayHasKey('seo', $content['data']['page']);
        $this->assertEquals($menu->getId(), $content['data']['page']['menus']['HEADER']['id']);

        $this->client->request(
            'GET',
            $this->router->generate('api_page_find', [
                'api_version' => self::API_VERSION,
                'slug' => $slug,
                'locale' => 'fr',
                'show_menus' => false,
                'show_tags' => false,
                'show_statistiques' => false,
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetour($content);
        $this->assertArrayNotHasKey('statistiques', $content['data']['page']);
        $this->assertArrayNotHasKey('menus', $content['data']['page']);
        $this->assertArrayNotHasKey('tags', $content['data']['page']);
        $this->assertArrayHasKey('seo', $content['data']['page']);

        $this->client->request(
            'GET',
            $this->router->generate('api_page_find', [
                'api_version' => self::API_VERSION,
                'slug' => $slug,
                'locale' => 'fr',
                'show_menus' => true,
                'show_tags' => false,
                'show_statistiques' => true,
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetour($content);
        $this->assertArrayHasKey('statistiques', $content['data']['page']);
        $this->assertArrayHasKey('menus', $content['data']['page']);
        $this->assertArrayNotHasKey('tags', $content['data']['page']);
        $this->assertArrayHasKey('seo', $content['data']['page']);
    }

    /**
     * Test méthode find()
     * @return void
     */
    public function testFindWrongParameter(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('api_page_find', [
                'api_version' => self::API_VERSION,
                'slug' => 'azerty',
                'locale' => 'fre',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            'Choisir une locale entre fr (français) ou es (espagnol) ou en (anglais) ',
            $content['errors'][0],
        );
    }

    /**
     * Test méthode find()
     * Test avec id qui n'existe pas
     * @return void
     */
    public function testFindBadSlug(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $this->client->request(
            'GET',
            $this->router->generate('api_page_find', [
                'api_version' => self::API_VERSION,
                'slug' => 'azerty',
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.find.page.not.found', domain: 'api_errors'),
            $content['errors'][0],
        );
    }

    /**
     * Test méthode find()
     * Récupération lendingPage
     * @return void
     */
    public function testFindLendingPage(): void
    {
        $page = $this->createPageAllDataDefault();
        $page->setLandingPage(true);
        $menu = $this->createMenuAllDataDefault();

        $page->addMenu($menu);
        $this->em->persist($page);
        $this->em->flush();

        $this->client->request(
            'GET',
            $this->router->generate('api_page_find', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test méthode getContentInPage()
     * @return void
     */
    public function testGetContentInPage(): void
    {
        $page = $this->createPageAllDataDefault();
        $pageContent = $page->getPageContents()->first();

        $this->client->request(
            'GET',
            $this->router->generate('api_page_content', [
                'api_version' => self::API_VERSION,
                'id' => $pageContent->getId(),
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
        $this->checkStructureApiRetour($content);
    }

    /**
     * Test méthode getContentInPage() mauvais paramètre
     * @return void
     */
    public function testGetContentInPageWrongParameter(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('api_page_content', [
                'api_version' => self::API_VERSION,
                'id' => 1,
                'locale' => 'fre',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            'Choisir une locale entre fr (français) ou es (espagnol) ou en (anglais) ',
            $content['errors'][0],
        );
    }

    /**
     * Test méthode getContentInPage()
     * Test avec id qui n'existe pas
     * @return void
     */
    public function testGetContentInPageBadId(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $this->client->request(
            'GET',
            $this->router->generate('api_page_content', [
                'api_version' => self::API_VERSION,
                'id' => 0,
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.find.page.content.not.found', domain: 'api_errors'),
            $content['errors'][0],
        );
    }

    /**
     * Test méthode getPageByCategory()
     * @return void
     */
    public function testGetPageByCategory(): void
    {
        $page = $this->createPageAllDataDefault();
        $pageTranslation = $page->getPageTranslationByLocale('fr');

        $this->client->request(
            'GET',
            $this->router->generate('api_page_category', [
                'api_version' => self::API_VERSION,
                'category' => 'page',
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetour($content);

        $pageReturn = $content['data']['pages'][0];
        $this->assertArrayHasKey('title', $pageReturn);
        $this->assertEquals($pageTranslation->getTitre(), $pageReturn['title']);
        $this->assertArrayHasKey('slug', $pageReturn);
        $this->assertEquals($pageTranslation->getUrl(), $pageReturn['slug']);

        $this->assertArrayHasKey('rows', $content['data']);
        $this->assertEquals(1, $content['data']['rows']);
    }

    /**
     * Test méthode getPageByCategory()
     * Test une mauvaise locale
     * @return void
     */
    public function testGetPageByCategoryWrongParameter(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('api_page_category', [
                'api_version' => self::API_VERSION,
                'category' => 'page',
                'locale' => 'fre',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            'Choisir une locale entre fr (français) ou es (espagnol) ou en (anglais) ',
            $content['errors'][0],
        );
    }

    /**
     * Test méthode getPageByCategory()
     * Test avec une catégorie qui n'existe pas
     * @return void
     */
    public function testGetPageByCategoryBadCategory(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $this->client->request(
            'GET',
            $this->router->generate('api_page_category', [
                'api_version' => self::API_VERSION,
                'category' => 'unit-test',
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.find.listing.category.not.found', domain: 'api_errors'),
            $content['errors'][0],
        );
    }

    /**
     * Test méthode getPageByTag()
     * @return void
     */
    public function testGetPageByTag(): void
    {
        $page = $this->createPageAllDataDefault();
        $pageTranslation = $page->getPageTranslationByLocale('fr');
        $tag = $page->getTags()->first()->getTagTranslationByLocale('fr');

        $this->client->request(
            'GET',
            $this->router->generate('api_page_tag', [
                'api_version' => self::API_VERSION,
                'tag' => $tag->getLabel(),
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetour($content);
        $pageReturn = $content['data']['pages'][0];
        $this->assertArrayHasKey('title', $pageReturn);
        $this->assertEquals($pageTranslation->getTitre(), $pageReturn['title']);
        $this->assertArrayHasKey('slug', $pageReturn);
        $this->assertEquals($pageTranslation->getUrl(), $pageReturn['slug']);

        $this->assertArrayHasKey('rows', $content['data']);
        $this->assertEquals(1, $content['data']['rows']);
    }

    /**
     * Test méthode getPageByTag()
     * Test une mauvaise locale
     * @return void
     */
    public function testGetPageByTagWrongParameter(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('api_page_tag', [
                'api_version' => self::API_VERSION,
                'tag' => 'page',
                'locale' => 'fre',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            'Choisir une locale entre fr (français) ou es (espagnol) ou en (anglais) ',
            $content['errors'][0],
        );
    }

    /**
     * Test méthode getPageByTag()
     * Test avec un tag qui n'existe pas
     * @return void
     */
    public function testGetPageByTagBadTag(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $this->client->request(
            'GET',
            $this->router->generate('api_page_tag', [
                'api_version' => self::API_VERSION,
                'tag' => 'unit-test',
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.find.listing.tag.not.found', domain: 'api_errors'),
            $content['errors'][0],
        );
    }
}
