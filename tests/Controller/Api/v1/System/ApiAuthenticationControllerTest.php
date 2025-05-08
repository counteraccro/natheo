<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Tests\Controller\Api\v1\System;

use App\Tests\Controller\Api\AppApiTestCase;
use App\Utils\System\User\Role;

class ApiAuthenticationControllerTest extends AppApiTestCase
{
    /**
     * Test méthode auth()
     * @return void
     */
    public function testAuth() :void
    {
        $this->checkBadApiToken('api_authentication_auth');

        $this->client->request('GET', $this->router->generate('api_authentication_auth', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(self::HEADER_WRITE)
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);
        $this->assertContains('ROLE_WRITE_API', $content['data']['roles']);
    }

    /**
     * Test méthode authUser()
     * @return void
     */
    public function testAuthUser() :void
    {
        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server:  $this->getCustomHeaders(),
            content:  json_encode($this->getUserAuthParams([]))
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);
    }

    /**
     * Test méthode authUser()
     * @return void
     */
    public function testAuthUserBadParameter() :void
    {
        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server:  $this->getCustomHeaders(),
            content:  json_encode($this->getUserAuthParams([], role: 'bad_parameter'))
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);
        dd($content);
    }
}