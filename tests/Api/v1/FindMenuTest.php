<?php

namespace App\Tests\Api\v1;

use Api\v1\AppApiTest;
use App\Entity\Admin\Content\Menu\Menu;
use App\Utils\System\User\Role;

class FindMenuTest extends AppApiTest
{
    private const URL_FIND_MENU = self::URL_REF . '/menu/find';

    /**
     * Test avec id et page_slug en même temps
     * @return void
     */
    public function testWrongParameter(): void
    {
        $parameters = "?id=15&page_slug=bienvenue&position=4&locale=fr";

        $client = static::createClient();
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test avec id ou page_slug manquant
     * @return void
     */
    public function testMissingParameter(): void
    {
        $parameters = "?position=4&locale=fr";
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
    public function testBadId(): void
    {
        $parameters = "?id=0&position=4&locale=fr";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test menu avec un slug mais mauvais token
     * @return void
     */
    public function testMenuBySlugWrongToken(): void
    {
        $parameters = "?page_slug=bienvenue&position=4&locale=fr";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_WRONG)
        );
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test menu avec un slug
     * @return void
     */
    public function testMenuBySlug(): void
    {
        $parameters = "?page_slug=bienvenue&position=4&locale=fr";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test menu avec un id valide
     * @return void
     */
    public function testMenuById(): void
    {
        $client = static::createClient();
        $id = $this->getLastIdByEntity(Menu::class);


        $parameters = "?id=" . $id . "&position=4&locale=fr";
        $client->request('GET', self::URL_FIND_MENU . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
