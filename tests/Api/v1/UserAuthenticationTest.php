<?php

namespace App\Tests\Api\v1;

use Api\v1\AppApiTest;
use App\Utils\System\User\Role;

class UserAuthenticationTest extends AppApiTest
{
    private const URL_AUTHENTICATION_USER = self::URL_REF . '/authentication/user';

    /**
     * Test un token invalide
     * @return void
     */
    public function testWrongToken(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL_AUTHENTICATION_USER,
            $this->getUserAuthParams([], Role::ROLE_CONTRIBUTEUR),
            server: $this->getCustomHeaders(self::HEADER_WRONG)
        );
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $jsonString = '{"code_http":401,"message":"Token Invalide"}';
        $this->assertJsonStringEqualsJsonString($jsonString, $response->getContent());
    }

    /**
     * Test un mauvais paramètre
     * @return void
     */
    public function testWrongParams(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL_AUTHENTICATION_USER,
            ['json' => $this->getUserAuthParams([], 'bad_parameter')],
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();
        var_dump($response->getContent());
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
