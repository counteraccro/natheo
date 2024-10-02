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
            server: $this->getCustomHeaders(self::HEADER_READ),
            content:  json_encode($this->getUserAuthParams([], 'bad_parameter'))
        );
        $response = $client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertStringContainsString('username', json_decode($response->getContent(), true)['errors'][0], 'username non présent');
        $this->assertStringContainsString('password', json_decode($response->getContent(), true)['errors'][1], 'password non présent');
    }
}
