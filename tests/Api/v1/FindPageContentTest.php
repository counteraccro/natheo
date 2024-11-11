<?php

namespace App\Tests\Api\v1;

use Api\v1\AppApiTest;

class FindPageContentTest extends AppApiTest
{
    private const URL_FIND_PAGE_CONTENT = self::URL_REF . '/page/content';

    /**
     * Test une mauvaise locale
     * @return void
     */
    public function testWrongParameter(): void
    {
        $parameters = "?id=123&locale=er";

        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_CONTENT . $parameters,
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
        $parameters = "?id=0&locale=fr";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_CONTENT . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }



    /**
     * Test good id
     * @return void
     */
    public function testIdContent()
    {
        $parameters = "?id=118";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_CONTENT . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
