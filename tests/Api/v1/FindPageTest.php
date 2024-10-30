<?php

namespace App\Tests\Api\v1;

use Api\v1\AppApiTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FindPageTest extends AppApiTest
{
    private const URL_FIND_MENU = self::URL_REF . '/page/find';

    /**
     * Test une mauvaise locale
     * @return void
     */
    public function testWrongParameter(): void
    {
        $parameters = "?slug=new-in-natheo-cms&locale=er";

        $client = static::createClient();
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test avec id qui n'existe pas
     * @return void
     */
    public function testBadSlug(): void
    {
        $parameters = "?slug=existe-pas&locale=fr";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }


    /**
     * Test la page par défaut
     * @return void
     */
    public function testLandingPage()
    {
        $parameters = "";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test le slug
     * @return void
     */
    public function testSlug()
    {
        $parameters = "?slug=bienvenue";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
