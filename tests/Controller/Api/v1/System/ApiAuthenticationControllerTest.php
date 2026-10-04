<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test authentification API
 */

namespace App\Tests\Controller\Api\v1\System;

use App\Entity\Admin\System\ApiToken;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\Controller\Api\AppApiTestCase;
use App\Utils\System\ApiToken\TokenHasher;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiAuthenticationControllerTest extends AppApiTestCase
{
    /**
     * Test méthode auth()
     * @return void
     */
    public function testAuth(): void
    {
        $this->checkBadApiToken('api_authentication_auth');

        $this->client->request(
            'GET',
            $this->router->generate('api_authentication_auth', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(self::HEADER_WRITE),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);
        $this->assertContains('ROLE_WRITE_API', $content['data']['roles']);
    }

    /**
     * Test authentification par token : hash, expiration, date de dernière utilisation et format du header
     * @return void
     */
    public function testAuthApiToken(): void
    {
        $url = $this->router->generate('api_authentication_auth', ['api_version' => self::API_VERSION]);
        $headers = ['HTTP_Accept' => 'application/json', 'HTTP_Content-Type' => 'application/json'];

        $apiToken = $this->createApiToken([
            'token' => TokenHasher::hash('token-valide'),
            'roles' => ['ROLE_READ_API'],
            'disabled' => false,
        ]);
        $this->assertNull($apiToken->getLastUsedAt());

        $this->client->request('GET', $url, server: $headers + ['HTTP_Authorization' => 'Bearer token-valide']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->em->clear();
        $this->assertNotNull($this->em->getRepository(ApiToken::class)->find($apiToken->getId())->getLastUsedAt());

        // Le hash stocké en base ne permet pas de s'authentifier
        $this->client->request(
            'GET',
            $url,
            server: $headers + ['HTTP_Authorization' => 'Bearer ' . TokenHasher::hash('token-valide')],
        );
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // Le header doit commencer par "Bearer "
        $this->client->request('GET', $url, server: $headers + ['HTTP_Authorization' => 'xxBearer token-valide']);
        $this->assertNotEquals(200, $this->client->getResponse()->getStatusCode());

        $this->createApiToken([
            'token' => TokenHasher::hash('token-expire'),
            'roles' => ['ROLE_READ_API'],
            'disabled' => false,
            'expiresAt' => new \DateTime('-1 day'),
        ]);
        $this->client->request('GET', $url, server: $headers + ['HTTP_Authorization' => 'Bearer token-expire']);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test méthode authUser()
     * @return void
     */
    public function testAuthUser(): void
    {
        // User classique
        $this->client->request(
            'POST',
            $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode($this->getUserAuthParams([])),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        // Admin
        $password = self::getFaker()->password();
        $this->client->request(
            'POST',
            $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode(
                $this->getUserAuthParams(
                    [],
                    $this->createUserAdmin(['password' => $password, 'disabled' => false, 'anonymous' => false]),
                    $password,
                ),
            ),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        // superAdmin
        $password = self::getFaker()->password();
        $this->client->request(
            'POST',
            $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode(
                $this->getUserAuthParams(
                    [],
                    $this->createUserSuperAdmin(['password' => $password, 'disabled' => false, 'anonymous' => false]),
                    $password,
                ),
            ),
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
    public function testAuthUserBadParameter(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $this->client->request(
            'POST',
            $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode($this->getUserAuthParams([], role: 'bad_parameter')),
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.params.name.not.found', ['param' => 'username'], domain: 'api_errors'),
            $content['errors'][0],
        );
    }

    /**
     * Test méthode authUser()
     * @return void
     */
    public function testAuthUserBadValue(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $this->client->request(
            'POST',
            $this->router->generate('api_authentication_auth_user', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
            content: json_encode($this->getUserAuthParams([], role: 'bad_type')),
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetourError($content);
        $this->assertEquals(
            $translator->trans('api_errors.user.token.not.found', domain: 'api_errors'),
            $content['errors'][0],
        );
    }

    /**
     * Test retour API fermé
     * @return void
     */
    public function testCloseApi(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_OPEN_SITE->value, '0');

        $this->client->request(
            'GET',
            $this->router->generate('api_authentication_auth', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(self::HEADER_WRITE),
        );
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertStringContainsString(
            $translator->trans('api_errors.api.not.open', domain: 'api_errors'),
            $content['errors'][0],
        );
    }
}
