<?php

namespace App\Tests\Api\v1;

use Api\v1\AppApiTest;

class FindPageCategoryTest extends AppApiTest
{
    private const URL_FIND_PAGE_CATEGORY = self::URL_REF . '/page/category';

    /**
     * Test une mauvaise locale
     * @return void
     */
    public function testWrongParameter(): void
    {
        $parameters = "?category=page&locale=er";

        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_CATEGORY . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test avec une catégorie qui n'existe pas qui n'existe pas
     * @return void
     */
    public function testBadId(): void
    {
        $parameters = "?category=no-cat&locale=fr";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_CATEGORY . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test good category
     * @return void
     */
    public function testCategory()
    {
        $parameters = "?category=page";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_CATEGORY . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
