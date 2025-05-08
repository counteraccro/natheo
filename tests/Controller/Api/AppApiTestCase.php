<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Tests\Controller\Api;

use App\Entity\Admin\System\ApiToken;
use App\Entity\Admin\System\User;
use App\Tests\AppWebTestCase;
use App\Utils\Api\Parameters\ApiParametersUserAuthRef;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppApiTestCase  extends AppWebTestCase
{

    protected const API_VERSION = 'v1';

    /**
     * Code pour générer un header avec un mauvais token
     * @var int
     */
    protected const HEADER_WRONG = 1;

    /**
     * Code pour générer un header avec un token accès lecture
     * @var int
     */
    protected const HEADER_READ = 2;

    /**
     * Code pour générer un header avec un token accès écriture
     * @var int
     */
    protected const HEADER_WRITE = 3;

    /**
     * Code pour générer un header avec un token accès admin
     * @var int
     */
    protected const HEADER_ADMIN = 4;

    /**
     * Génère le header pour tester les APIs
     * @param int $typeHeader
     * @return array
     */
    protected function getCustomHeaders(int $typeHeader = self::HEADER_READ): array
    {
        $apiToken = match ($typeHeader) {
            self::HEADER_WRONG => 123,
            self::HEADER_READ => $this->createApiToken(['roles' => ['ROLE_READ_API'], 'disabled' => false]),
            self::HEADER_WRITE => $this->createApiToken(['roles' => ['ROLE_WRITE_API'], 'disabled' => false]),
            self::HEADER_ADMIN => $this->createApiToken(['roles' => ['ROLE_ADMIN_API'], 'disabled' => false]),
            default => '',
        };

        if ($apiToken instanceof ApiToken) {
            $token = $apiToken->getToken();
        } else {
            $token = $apiToken;
        }

        return [
            'HTTP_Accept' => 'application/json',
            'HTTP_Content-Type' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $token
        ];

    }

    /**
     * Verification bad token
     * @param string $route
     * @param array $params
     * @param string $methode
     * @return void
     */
    protected function checkBadApiToken(string $route, array $params = [], string $methode = 'GET'): void
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);

        $params = array_merge($params, ['api_version' => self::API_VERSION]);

        $this->client->request($methode, $this->router->generate($route, $params),
            server: $this->getCustomHeaders(self::HEADER_WRONG)
        );
        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code_http', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('errors', $content);
        $this->assertStringContainsString($translator->trans('api_errors.authentication.failure',  domain: 'api_errors'), $content['errors'][0]);
    }

    /**
     * Retourne les informations d'authentification en fonction du role demandé
     * @param array $params
     * @param User|null $user
     * @param string|null $role
     * @return array|string[]
     */
    protected function getUserAuthParams(array $params, ?User $user = null, ?string $role = null): array
    {
        $password = self::getFaker()->password();
        if($user === null) {
            $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false, 'password' => $password]);
        }

        if ($role === 'bad_parameter') {
            $userAuth = [
                'username2' => $user->getEmail(),
                'password2' => $password,
            ];
        } else if ($role === 'bad_type') {
            $userAuth = [
                ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_USERNAME => 22,
                ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_PASSWORD => true
            ];
        } else {
            $userAuth = [
                ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_USERNAME => $user->getEmail(),
                ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER_PASSWORD => $password
            ];
        }
        return array_merge($params, $userAuth);
    }

    /**
     * Test la structure de retour de l'API
     * @param array $apiReturn
     * @return void
     */
    protected function checkStructureApiRetour(array $apiReturn): void
    {
        $this->assertArrayHasKey('code_http', $apiReturn);
        $this->assertArrayHasKey('message', $apiReturn);
        $this->assertArrayHasKey('data', $apiReturn);
    }
}