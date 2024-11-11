<?php

namespace Api\v1;

use Api\v1\AppApiTest;

class FindPageTagTest extends AppApiTest
{
    private const URL_FIND_PAGE_TAG = self::URL_REF . '/page/tag';

    /**
     * Test une mauvaise locale
     * @return void
     */
    public function testWrongParameter(): void
    {
        $parameters = "?tag=Page&locale=er";

        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_TAG . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test avec une tag qui n'existe pas qui n'existe pas
     * @return void
     */
    public function testBadTag(): void
    {
        $parameters = "?tag=no-tag&locale=fr";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_TAG . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * Test good tag
     * @return void
     */
    public function testTag()
    {
        $parameters = "?tag=Page";
        $client = static::createClient();
        $client->request('GET', self::URL_FIND_PAGE_TAG . $parameters,
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
