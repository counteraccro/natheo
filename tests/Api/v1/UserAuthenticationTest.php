<?php

namespace App\Tests\Api\v1;

use Api\v1\AppApiTest;
use App\Entity\Admin\System\UserData;
use App\Service\Admin\System\User\UserService;
use App\Utils\Api\Parameters\ApiParametersUserAuthRef;
use App\Utils\Api\Tests\ApiUserAuthenticationTestConst;
use App\Utils\System\User\Role;
use App\Utils\System\User\UserDataKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
        $this->assertEquals(403, $response->getStatusCode(), '403 attendu');
        $this->assertJson($response->getContent());
        //$this->assertStringContainsString('username', json_decode($response->getContent(), true)['errors'][0], 'username non présent');
        //$this->assertStringContainsString('password', json_decode($response->getContent(), true)['errors'][1], 'password non présent');
    }

    /**
     * Test un mauvais type de paramètre
     * @return void
     */
    public function testWrongParamsType(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL_AUTHENTICATION_USER,
            server: $this->getCustomHeaders(self::HEADER_READ),
            content:  json_encode($this->getUserAuthParams([], 'bad_type'))
        );
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode(), '401 attendu');
        $this->assertJson($response->getContent());

        $this->assertStringContainsString('Utilisateur', json_decode($response->getContent(), true)['errors'][0], 'utilisateur non présent');
        //$this->assertStringContainsString('password', json_decode($response->getContent(), true)['errors'][1], 'password non présent');
    }

    /**
     * Test utilisateur not found
     * @return void
     */
    public function testUserNotFound(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL_AUTHENTICATION_USER,
            server: $this->getCustomHeaders(self::HEADER_READ),
            content:  json_encode($this->getUserAuthParams([], Role::ROLE_USER))
        );
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode(), '401 attendu');
        $this->assertJson($response->getContent());
        $this->assertContains('Utilisateur non trouvé', json_decode($response->getContent(), true)['errors'], "Le message d'erreur attendu ne correspond pas");
    }

    /**
     * Test de connexion de l'utilisateur contributeur
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUserContributeur(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL_AUTHENTICATION_USER,
            server: $this->getCustomHeaders(self::HEADER_READ),
            content:  json_encode($this->getUserAuthParams([], Role::ROLE_CONTRIBUTEUR))
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), '200 attendu');
        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);
        $token = $this->getTokenByUserRole(Role::ROLE_CONTRIBUTEUR);
        $this->assertEquals($token, $array['data']['token'], 'Token non égal');

    }

    /**
     * Test de connexion de l'utilisateur admin
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUserAdmin(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL_AUTHENTICATION_USER,
            server: $this->getCustomHeaders(self::HEADER_READ),
            content:  json_encode($this->getUserAuthParams([], Role::ROLE_ADMIN))
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), '200 attendu');
        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);
        $token = $this->getTokenByUserRole(Role::ROLE_ADMIN);
        $this->assertEquals($token, $array['data']['token'], 'Token non égal');

    }

    /**
     * Test de connexion de l'utilisateur super admin
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUserSuperAdmin(): void
    {
        $client = static::createClient();
        $client->request('POST', self::URL_AUTHENTICATION_USER,
            server: $this->getCustomHeaders(self::HEADER_READ),
            content:  json_encode($this->getUserAuthParams([], Role::ROLE_SUPER_ADMIN))
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), '200 attendu');
        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);
        $token = $this->getTokenByUserRole(Role::ROLE_SUPER_ADMIN);
        $this->assertEquals($token, $array['data']['token'], 'Token non égal');

    }

    /**
     * Retourne un token en fonction d'un role
     * @param string $role
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getTokenByUserRole(string $role): string
    {
        /** @var UserService $userService */
        $userService = $this->getServiceByClassName(UserService::class);

        $contributeur = ApiUserAuthenticationTestConst::AUTHENTICATION_USER_TAB[$role];
        $user = $userService->getUserByEmailAndPassword($contributeur[ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_USERNAME], $contributeur[ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_PASSWORD]);
        $liste = $user->getUserData()->filter(function(UserData $userData) {
            return $userData->getKey() === UserDataKey::KEY_TOKEN_CONNEXION;
        });
        return $liste[0]->getValue();
    }
}
