<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des ressources UserSession et UserSessionRefresh de l'API v2
 */

namespace App\Tests\Api\V2\System;

use App\Api\V2\Resource\System\UserSession;
use App\Entity\Admin\System\User;
use App\Tests\Api\V2\AppApiV2TestCase;

class UserSessionTest extends AppApiV2TestCase
{
    /**
     * Test POST /api/v2/user_sessions pour chaque rôle d'administration
     * @return void
     */
    public function testCreate(): void
    {
        $this->assertProblem(
            $this->requestV2('POST', '/user_sessions', self::HEADER_NONE, ['email' => 'a@b.fr', 'password' => 'x']),
            401,
        );

        foreach (['createUserContributeur', 'createUserAdmin', 'createUserSuperAdmin'] as $factory) {
            $password = self::getFaker()->password();
            /** @var User $user */
            $user = $this->$factory(['password' => $password, 'disabled' => false, 'anonymous' => false]);

            $response = $this->requestV2(
                'POST',
                '/user_sessions',
                body: [
                    'email' => $user->getEmail(),
                    'password' => $password,
                ],
            );
            $this->assertEquals(201, $response->getStatusCode(), $factory);
            $this->assertMatchesResourceItemJsonSchema(UserSession::class, '_api_/user_sessions_post');
            $content = $this->decode($response);

            $this->assertEquals('UserSession', $content['@type']);
            $this->assertEquals('/api/v2/user_sessions/' . $content['id'], $content['@id']);
            $this->assertNotEmpty($content['accessToken']);
            $this->assertNotEmpty($content['refreshToken']);
            $this->assertGreaterThan(
                new \DateTimeImmutable(),
                new \DateTimeImmutable($content['accessTokenExpiresAt']),
            );
            $this->assertGreaterThan(
                new \DateTimeImmutable($content['accessTokenExpiresAt']),
                new \DateTimeImmutable($content['refreshTokenExpiresAt']),
            );

            $me = $this->decode($this->requestV2('GET', '/me', headers: $this->bearer($content['accessToken'])));
            $this->assertEquals($user->getId(), $me['id']);
        }
    }

    /**
     * Identifiants faux, utilisateur sans rôle d'administration : 400 invalid_grant
     * @return void
     */
    public function testCreateRefused(): void
    {
        $password = self::getFaker()->password();
        $contributor = $this->createUserContributeur([
            'password' => $password,
            'disabled' => false,
            'anonymous' => false,
        ]);
        $user = $this->createUser(['password' => $password, 'disabled' => false, 'anonymous' => false]);
        $detail = $this->translator->trans('api_errors.session.invalid_credentials', domain: 'api_errors');

        foreach (
            [
                ['email' => $contributor->getEmail(), 'password' => 'bad-password'],
                ['email' => 'inconnu@natheo.fr', 'password' => $password],
                ['email' => $user->getEmail(), 'password' => $password],
            ]
            as $body
        ) {
            $response = $this->requestV2('POST', '/user_sessions', body: $body);
            $content = $this->assertProblem($response, 400);
            $this->assertEquals('/errors/invalid_grant', $content['type']);
            $this->assertEquals($detail, $content['detail']);
            $this->assertFalse($response->headers->has('WWW-Authenticate'));
        }
    }

    /**
     * Champs vides ou mal formés : 422
     * @return void
     */
    public function testCreateValidation(): void
    {
        foreach ([['email' => '', 'password' => ''], ['email' => 'pas-un-email', 'password' => 'x'], []] as $body) {
            $content = $this->assertProblem($this->requestV2('POST', '/user_sessions', body: $body), 422);
            $this->assertNotEmpty($content['violations']);
        }
    }

    /**
     * Limitation des tentatives par IP et email : 429 avec Retry-After
     * @return void
     */
    public function testCreateRateLimit(): void
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $body = ['email' => $user->getEmail(), 'password' => 'bad-password'];

        for ($i = 0; $i < 5; $i++) {
            $this->assertProblem($this->requestV2('POST', '/user_sessions', body: $body), 400);
        }

        $response = $this->requestV2('POST', '/user_sessions', body: $body);
        $content = $this->assertProblem($response, 429);
        $this->assertTrue($response->headers->has('Retry-After'));
        $this->assertEquals(
            $this->translator->trans('api_errors.too.many.requests', domain: 'api_errors'),
            $content['detail'],
        );
    }

    /**
     * Test POST /api/v2/user_session_refreshes : rotation, réutilisation qui ferme la session, autre application
     * @return void
     */
    public function testRefresh(): void
    {
        [$first, $apiHeaders] = $this->login();
        $detail = $this->translator->trans('api_errors.session.invalid_refresh_token', domain: 'api_errors');

        // Autre token d'API : refusé, session conservée
        $content = $this->assertProblem(
            $this->requestV2('POST', '/user_session_refreshes', body: ['refreshToken' => $first['refreshToken']]),
            400,
        );
        $this->assertEquals($detail, $content['detail']);

        $response = $this->requestV2(
            'POST',
            '/user_session_refreshes',
            body: ['refreshToken' => $first['refreshToken']],
            headers: $apiHeaders,
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(UserSession::class, '_api_/user_sessions_post');
        $second = $this->decode($response);
        $this->assertEquals($first['id'], $second['id']);
        $this->assertNotEquals($first['accessToken'], $second['accessToken']);
        $this->assertNotEquals($first['refreshToken'], $second['refreshToken']);
        $this->assertProblem($this->requestV2('GET', '/me', headers: $this->bearer($first['accessToken'])), 401);
        $this->assertEquals(
            200,
            $this->requestV2('GET', '/me', headers: $this->bearer($second['accessToken']))->getStatusCode(),
        );

        // Ancien refresh token réutilisé : session fermée
        $this->assertProblem(
            $this->requestV2(
                'POST',
                '/user_session_refreshes',
                body: ['refreshToken' => $first['refreshToken']],
                headers: $apiHeaders,
            ),
            400,
        );
        $this->assertProblem($this->requestV2('GET', '/me', headers: $this->bearer($second['accessToken'])), 401);

        $this->assertProblem($this->requestV2('POST', '/user_session_refreshes', body: ['refreshToken' => '']), 422);
    }

    /**
     * Test GET /api/v2/user_sessions : sessions de l'utilisateur, celle de la requête marquée
     * @return void
     */
    public function testGetCollection(): void
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $headers = $this->sessionHeaders($user);
        $this->sessionHeaders($user);
        $this->sessionHeaders($this->createUserContributeur(['disabled' => false, 'anonymous' => false]));

        $content = $this->assertProblem($this->requestV2('GET', '/user_sessions'), 403);
        $this->assertEquals(
            $this->translator->trans('api_errors.session.required', domain: 'api_errors'),
            $content['detail'],
        );

        $content = $this->decode($this->requestV2('GET', '/user_sessions', headers: $headers));
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceCollectionJsonSchema(UserSession::class);
        $this->assertCount(2, $content['member']);
        $this->assertCount(1, array_filter($content['member'], fn(array $session) => $session['current']));
        $this->assertArrayNotHasKey('accessToken', $content['member'][0]);
        $this->assertArrayNotHasKey('refreshToken', $content['member'][0]);
    }

    /**
     * Test GET /api/v2/user_sessions/{id} : current, autre session de l'utilisateur, session d'un autre
     * @return void
     */
    public function testGetItem(): void
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $headers = $this->sessionHeaders($user);
        $other = $this->createUserSession(['user' => $user]);
        $foreign = $this->createUserSession();

        $current = $this->decode($this->requestV2('GET', '/user_sessions/current', headers: $headers));
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceItemJsonSchema(UserSession::class);
        $this->assertTrue($current['current']);
        $this->assertArrayNotHasKey('accessToken', $current);

        $content = $this->decode($this->requestV2('GET', '/user_sessions/' . $other->getId(), headers: $headers));
        $this->assertEquals($other->getId(), $content['id']);
        $this->assertFalse($content['current']);

        $this->assertProblem($this->requestV2('GET', '/user_sessions/' . $foreign->getId(), headers: $headers), 404);
        $this->assertProblem($this->requestV2('GET', '/user_sessions/999999999', headers: $headers), 404);
        $this->assertProblem($this->requestV2('GET', '/user_sessions/current'), 403);
    }

    /**
     * Test DELETE /api/v2/user_sessions/{id} : autre session, session d'un autre, puis current
     * @return void
     */
    public function testDelete(): void
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $headers = $this->sessionHeaders($user);
        $other = $this->createUserSession(['user' => $user]);
        $foreign = $this->createUserSession();

        $this->assertProblem($this->requestV2('DELETE', '/user_sessions/current'), 403);
        $this->assertProblem($this->requestV2('DELETE', '/user_sessions/' . $foreign->getId(), headers: $headers), 404);

        $this->assertEquals(
            204,
            $this->requestV2('DELETE', '/user_sessions/' . $other->getId(), headers: $headers)->getStatusCode(),
        );
        $this->assertProblem($this->requestV2('GET', '/user_sessions/' . $other->getId(), headers: $headers), 404);

        $this->assertEquals(
            204,
            $this->requestV2('DELETE', '/user_sessions/current', headers: $headers)->getStatusCode(),
        );
        $this->assertProblem($this->requestV2('GET', '/me', headers: $headers), 401);
    }

    /**
     * Ouvre une session par l'API avec un nouveau token d'API
     * @return array{0: array, 1: array<string, string>} réponse de création et en-têtes du token d'API utilisé
     */
    private function login(): array
    {
        $password = self::getFaker()->password();
        $user = $this->createUserContributeur(['password' => $password, 'disabled' => false, 'anonymous' => false]);
        $apiHeaders = $this->getCustomHeaders();
        $content = $this->decode(
            $this->requestV2(
                'POST',
                '/user_sessions',
                self::HEADER_NONE,
                ['email' => $user->getEmail(), 'password' => $password],
                headers: ['HTTP_Authorization' => $apiHeaders['HTTP_Authorization']],
            ),
        );
        return [$content, ['HTTP_Authorization' => $apiHeaders['HTTP_Authorization']]];
    }

    /**
     * En-tête Authorization d'un access token
     * @param string $accessToken
     * @return array<string, string>
     */
    private function bearer(string $accessToken): array
    {
        return ['HTTP_Authorization' => 'Bearer ' . $accessToken];
    }
}
