<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource UserToken de l'API v2
 */

namespace App\Tests\Api\V2\System;

use App\Api\V2\Resource\System\UserToken;
use App\Entity\Admin\System\User;
use App\Service\Api\System\User\ApiUserService;
use App\Tests\Api\V2\AppApiV2TestCase;

class UserTokenTest extends AppApiV2TestCase
{
    /**
     * Test POST /api/v2/authentication/user pour chaque rôle d'administration
     * @return void
     */
    public function testAuthenticate(): void
    {
        $this->assertProblem(
            $this->requestV2('POST', '/authentication/user', self::HEADER_NONE, [
                'email' => 'a@b.fr',
                'password' => 'x',
            ]),
            401,
        );

        foreach (['createUserContributeur', 'createUserAdmin', 'createUserSuperAdmin'] as $factory) {
            $password = self::getFaker()->password();
            /** @var User $user */
            $user = $this->$factory(['password' => $password, 'disabled' => false, 'anonymous' => false]);

            $content = $this->decode(
                $this->requestV2(
                    'POST',
                    '/authentication/user',
                    body: [
                        'email' => $user->getEmail(),
                        'password' => $password,
                    ],
                ),
            );
            $this->assertResponseStatusCodeSame(200, $factory);
            $this->assertMatchesResourceItemJsonSchema(UserToken::class);
            $this->assertEquals('UserToken', $content['@type']);
            $this->assertNotEmpty($content['token']);
            $this->assertGreaterThan(new \DateTimeImmutable(), new \DateTimeImmutable($content['expiresAt']));

            $tokenUser = $this->container->get(ApiUserService::class)->getUserByUserToken($content['token']);
            $this->assertEquals($user->getId(), $tokenUser?->getId());
        }
    }

    /**
     * Identifiants faux, utilisateur sans rôle d'administration : 401 avec WWW-Authenticate
     * @return void
     */
    public function testAuthenticateRefused(): void
    {
        $password = self::getFaker()->password();
        $contributor = $this->createUserContributeur([
            'password' => $password,
            'disabled' => false,
            'anonymous' => false,
        ]);
        $user = $this->createUser(['password' => $password, 'disabled' => false, 'anonymous' => false]);
        $detail = $this->translator->trans('api_errors.user.not.found', domain: 'api_errors');

        foreach (
            [
                ['email' => $contributor->getEmail(), 'password' => 'bad-password'],
                ['email' => 'inconnu@natheo.fr', 'password' => $password],
                ['email' => $user->getEmail(), 'password' => $password],
            ]
            as $body
        ) {
            $response = $this->requestV2('POST', '/authentication/user', body: $body);
            $content = $this->assertProblem($response, 401);
            $this->assertEquals($detail, $content['detail']);
            $this->assertEquals('Bearer', $response->headers->get('WWW-Authenticate'));
        }
    }

    /**
     * Champs vides ou mal formés : 422
     * @return void
     */
    public function testAuthenticateValidation(): void
    {
        foreach ([['email' => '', 'password' => ''], ['email' => 'pas-un-email', 'password' => 'x'], []] as $body) {
            $content = $this->assertProblem($this->requestV2('POST', '/authentication/user', body: $body), 422);
            $this->assertNotEmpty($content['violations']);
        }
    }

    /**
     * Limitation des tentatives par IP et email : 429 avec Retry-After
     * @return void
     */
    public function testAuthenticateRateLimit(): void
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $body = ['email' => $user->getEmail(), 'password' => 'bad-password'];

        for ($i = 0; $i < 5; $i++) {
            $this->assertProblem($this->requestV2('POST', '/authentication/user', body: $body), 401);
        }

        $response = $this->requestV2('POST', '/authentication/user', body: $body);
        $content = $this->assertProblem($response, 429);
        $this->assertTrue($response->headers->has('Retry-After'));
        $this->assertEquals(
            $this->translator->trans('api_errors.too.many.requests', domain: 'api_errors'),
            $content['detail'],
        );
    }

    /**
     * Test DELETE /api/v2/authentication/user
     * @return void
     */
    public function testRevoke(): void
    {
        $token = $this->createUserToken($this->createUserContributeur());
        $headers = ['HTTP_X_USER_TOKEN' => $token];

        $this->assertProblem(
            $this->requestV2('DELETE', '/authentication/user', self::HEADER_NONE, headers: $headers),
            401,
        );

        $response = $this->requestV2('DELETE', '/authentication/user', headers: $headers);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($this->container->get(ApiUserService::class)->getUserByUserToken($token));

        $detail = $this->translator->trans('api_errors.user.token.not.found', domain: 'api_errors');
        // Token révoqué, en-tête absent
        foreach ([$headers, []] as $revokedHeaders) {
            $content = $this->assertProblem(
                $this->requestV2('DELETE', '/authentication/user', headers: $revokedHeaders),
                403,
            );
            $this->assertEquals($detail, $content['detail']);
        }
    }
}
