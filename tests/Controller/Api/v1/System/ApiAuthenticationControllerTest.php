<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test authentification API
 */

namespace App\Tests\Controller\Api\v1\System;

use App\Service\Admin\System\OptionSystemService;
use App\Tests\Controller\Api\AppApiTestCase;
use App\Utils\System\Options\OptionSystemKey;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        // User classique
        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server:  $this->getCustomHeaders(),
            content:  json_encode($this->getUserAuthParams([]))
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        // Admin
        $password = self::getFaker()->password();
        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server:  $this->getCustomHeaders(),
            content:  json_encode($this->getUserAuthParams([], $this->createUserAdmin(['password' => $password, 'disabled' => false, 'anonymous' => false]), $password))
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        // superAdmin
        $password = self::getFaker()->password();
        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server:  $this->getCustomHeaders(),
            content:  json_encode($this->getUserAuthParams([], $this->createUserSuperAdmin(['password' => $password, 'disabled' => false, 'anonymous' => false]), $password))
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
        $translator = $this->container->get(TranslatorInterface::class);

        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server:  $this->getCustomHeaders(),
            content:  json_encode($this->getUserAuthParams([], role: 'bad_parameter'))
        );
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('detail', $content);
        $this->assertStringContainsString($translator->trans('api_errors.params.name.not.found', ['param' => 'username'], domain: 'api_errors'), $content['detail']);
    }

    /**
     * Test méthode authUser()
     * @return void
     */
    public function testAuthUserBadValue() :void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $this->client->request('POST', $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server:  $this->getCustomHeaders(),
            content:  json_encode($this->getUserAuthParams([], role: 'bad_type'))
        );
        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('detail', $content);
        $this->assertStringContainsString($translator->trans('api_errors.user.token.not.found', domain: 'api_errors'), $content['detail']);
    }

    /**
     * Test retour API fermé
     * @return void
     */
    public function testCloseApi(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_OPEN_SITE, 0);

        $this->client->request('GET', $this->router->generate('api_authentication_auth', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(self::HEADER_WRITE)
        );
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertStringContainsString($translator->trans('api_errors.api.not.open', domain: 'api_errors'), $content['errors'][0]);
    }
}