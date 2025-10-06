<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Tests\Controller\Api\v1\Content;

use App\Tests\Controller\Api\AppApiTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiMenuControllerTest extends AppApiTestCase
{
    /**
     * Test méthode find()
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

        // Get menu by pageSlug
        $this->client->request(
            'GET',
            $this->router->generate('api_menu_find', [
                'api_version' => self::API_VERSION,
                'page_slug' => $slug,
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(self::HEADER_READ),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetour($content);
        $this->assertEquals($content['data']['id'], $menu->getId());

        $nb = 0;
        foreach ($menu->getMenuElements() as $menuElement) {
            if ($menuElement->getParent() === null && !$menuElement->isDisabled()) {
                $nb++;
            }
        }
        $this->assertCount($nb, $content['data']['elements']);

        $menu = $this->createMenuAllDataDefault();
        // Get menu By Id
        $this->client->request(
            'GET',
            $this->router->generate('api_menu_find', [
                'api_version' => self::API_VERSION,
                'id' => $menu->getId(),
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(self::HEADER_READ),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetour($content);
        $this->assertEquals($content['data']['id'], $menu->getId());

        $nb = 0;
        foreach ($menu->getMenuElements() as $menuElement) {
            if ($menuElement->getParent() === null && !$menuElement->isDisabled()) {
                $nb++;
            }
        }
        $this->assertCount($nb, $content['data']['elements']);
    }

    /**
     * Test avec id et page_slug en même temps
     * @return void
     */
    public function testWrongParameter(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $this->client->request(
            'GET',
            $this->router->generate('api_menu_find', [
                'api_version' => self::API_VERSION,
                'page_slug' => 'azerty',
                'id' => '2',
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(self::HEADER_READ),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.find.menu.id.slug.together', domain: 'api_errors'),
            $content['errors'][0],
        );
    }

    /**
     * Test avec id ou page_slug manquant
     * @return void
     */
    public function testMissingParameter(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $this->client->request(
            'GET',
            $this->router->generate('api_menu_find', ['api_version' => self::API_VERSION, 'locale' => 'fr']),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.find.menu.not.id.slug.together', domain: 'api_errors'),
            $content['errors'][0],
        );
    }

    /**
     * Test avec id qui n'existe pas
     * @return void
     */
    public function testBadId(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $this->client->request(
            'GET',
            $this->router->generate('api_menu_find', [
                'api_version' => self::API_VERSION,
                'id' => '2',
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(self::HEADER_READ),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.find.menu.not.found', domain: 'api_errors'),
            $content['errors'][0],
        );
    }

    /**
     * Test menu avec un slug mais mauvais token
     * @return void
     */
    public function testMenuBySlugWrongToken(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $this->client->request(
            'GET',
            $this->router->generate('api_menu_find', [
                'api_version' => self::API_VERSION,
                'page_slug' => 'azerty',
                'locale' => 'fr',
            ]),
            server: $this->getCustomHeaders(self::HEADER_WRONG),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.authentication.failure', domain: 'api_errors'),
            $content['errors'][0],
        );
    }
}
