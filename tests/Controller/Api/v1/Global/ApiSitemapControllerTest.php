<?php
/**
 * Test unitaire API sitemap
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Controller\Api\v1\Global;

use App\Tests\Controller\Api\AppApiTestCase;

class ApiSitemapControllerTest extends AppApiTestCase
{
    /**
     * Test la méthode getSitemap()
     * @return void
     */
    public function testGetSitemap() :void {

        $this->client->request('GET', $this->router->generate('api_sitemap_sitemap', ['api_version' => self::API_VERSION, 'locale' => 'fr']),
            server: $this->getCustomHeaders()
        );
        $response = $this->client->getResponse();;
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        dd($content);
    }
}