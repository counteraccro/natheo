<?php

namespace App\Tests\Api\v1;

use Api\v1\AppApiTest;
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
            //$this->getUserAuthParams([], Role::ROLE_CONTRIBUTEUR),
            //server: $this->getCustomHeaders(self::HEADER_WRONG)
        );
        $response = $client->getResponse();

        //var_dump($response);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
