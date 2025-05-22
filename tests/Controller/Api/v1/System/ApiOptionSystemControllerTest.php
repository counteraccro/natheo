<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test API option system
 */

namespace Controller\Api\v1\System;

use App\Tests\Controller\Api\AppApiTestCase;

class ApiOptionSystemControllerTest extends AppApiTestCase
{
    /**
     * Test méthode listing()
     * @return void
     */
    public function testListing(): void
    {
        $this->client->request('GET', $this->router->generate('api_options_systems_listing', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders()
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
    }
}