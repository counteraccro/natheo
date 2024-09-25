<?php

namespace App\Tests\Api;

use Api\AppApiTest;

class AuthenticationTest extends AppApiTest
{
    /**
     * Test un token invalide
     * @return void
     */
    public function testWrongToken(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URL_REF . '/authentication',
            server: $this->getCustomHeaders(self::HEADER_WRONG)
        );
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $jsonString = '{"code_http":401,"message":"Token Invalide"}';
        $this->assertJsonStringEqualsJsonString($jsonString, $response->getContent());
    }

    /**
     * Test le token d'accès en lecture
     * @return void
     */
    public function testReadToken(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URL_REF . '/authentication',
            server: $this->getCustomHeaders(self::HEADER_READ)
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Le token doit être valide');
        $this->assertJson($response->getContent());
        $this->assertContains('ROLE_READ_API', json_decode($response->getContent(), true)['data']['roles'], 'ROLE_READ_API non présent');
    }

    /**
     * Test le token d'accès en écriture
     * @return void
     */
    public function testWriteToken(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URL_REF . '/authentication',
            server: $this->getCustomHeaders(self::HEADER_WRITE)
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Le token doit être valide');
        $this->assertJson($response->getContent());
        $this->assertContains('ROLE_WRITE_API', json_decode($response->getContent(), true)['data']['roles'], 'ROLE_WRITE_API non présent');
    }

    /**
     * Test le token d'accès en admin
     * @return void
     */
    public function testAdminToken(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URL_REF . '/authentication',
            server: $this->getCustomHeaders(self::HEADER_ADMIN)
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Le token doit être valide');
        $this->assertJson($response->getContent());
        $this->assertContains('ROLE_ADMIN_API', json_decode($response->getContent(), true)['data']['roles'], 'ROLE_ADMIN_API non présent');
    }
}
