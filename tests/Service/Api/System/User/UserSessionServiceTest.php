<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests du service des sessions API des utilisateurs
 */

namespace App\Tests\Service\Api\System\User;

use App\Entity\Admin\System\UserSession;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Api\System\User\UserSessionService;
use App\Service\Api\System\User\UserSessionTokens;
use App\Service\Global\Token\TokenHasher;
use App\Tests\AppWebTestCase;

class UserSessionServiceTest extends AppWebTestCase
{
    private UserSessionService $userSessionService;

    public function setUp(): void
    {
        parent::setUp();
        $this->userSessionService = $this->container->get(UserSessionService::class);
    }

    /**
     * Test méthode authenticate() : rôles d'administration acceptés, identifiants faux et ROLE_USER seul refusés
     * @return void
     */
    public function testAuthenticate(): void
    {
        $password = self::getFaker()->password();
        $contributor = $this->createUserContributeur(['password' => $password, 'disabled' => false]);
        $user = $this->createUser(['password' => $password, 'disabled' => false]);

        $result = $this->userSessionService->authenticate($contributor->getEmail(), $password, '127.0.0.1');
        $this->assertEquals($contributor->getId(), $result->user?->getId());
        $this->assertFalse($result->isThrottled());

        foreach (
            [
                [$contributor->getEmail(), 'bad-password'],
                ['inconnu@natheo.fr', $password],
                [$user->getEmail(), $password],
            ]
            as [$email, $pass]
        ) {
            $result = $this->userSessionService->authenticate($email, $pass, '127.0.0.1');
            $this->assertNull($result->user);
            $this->assertFalse($result->isThrottled());
        }
    }

    /**
     * Test méthode authenticate() : limite par IP et email, remise à zéro par un succès
     * @return void
     */
    public function testAuthenticateThrottled(): void
    {
        $password = self::getFaker()->password();
        $email = $this->createUserContributeur(['password' => $password, 'disabled' => false])->getEmail();

        for ($i = 0; $i < 4; $i++) {
            $this->userSessionService->authenticate($email, 'bad-password', '10.0.0.1');
        }
        $this->assertNotNull($this->userSessionService->authenticate($email, $password, '10.0.0.1')->user);

        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(
                $this->userSessionService->authenticate($email, 'bad-password', '10.0.0.1')->isThrottled(),
            );
        }
        $result = $this->userSessionService->authenticate($email, $password, '10.0.0.1');
        $this->assertTrue($result->isThrottled());
        $this->assertNull($result->user);
        $this->assertGreaterThan(0, $result->retryAfter);

        // Autre IP : compteur distinct
        $this->assertNotNull($this->userSessionService->authenticate($email, $password, '10.0.0.2')->user);
    }

    /**
     * Test méthode create() : tokens stockés hachés, durées, sessions expirées de l'utilisateur purgées
     * @return void
     */
    public function testCreate(): void
    {
        $user = $this->createUserContributeur();
        $apiToken = $this->createApiToken(['disabled' => false]);
        $expired = $this->createUserSession([
            'user' => $user,
            'refreshTokenExpiresAt' => new \DateTimeImmutable('-1 second'),
        ]);
        $active = $this->createUserSession(['user' => $user]);
        $expiredId = $expired->getId();

        $tokens = $this->userSessionService->create($user, $apiToken, 'Navigateur', '127.0.0.1');
        $session = $tokens->session;

        $this->assertNotNull($session->getId());
        $this->assertSame(TokenHasher::hash($tokens->accessToken), $session->getAccessTokenHash());
        $this->assertSame(TokenHasher::hash($tokens->refreshToken), $session->getRefreshTokenHash());
        $this->assertNotSame($tokens->accessToken, $tokens->refreshToken);
        $this->assertSame($apiToken->getId(), $session->getApiToken()->getId());
        $this->assertSame('Navigateur', $session->getUserAgent());
        $this->assertSame('127.0.0.1', $session->getIp());
        $this->assertEqualsWithDelta(
            time() + UserSessionService::ACCESS_TOKEN_LIFETIME * 60,
            $session->getAccessTokenExpiresAt()->getTimestamp(),
            5,
        );
        $this->assertEqualsWithDelta(
            time() + $this->userSessionService->getRefreshTokenLifetime() * 60,
            $session->getRefreshTokenExpiresAt()->getTimestamp(),
            5,
        );

        $this->em->clear();
        $repository = $this->em->getRepository(UserSession::class);
        $this->assertNull($repository->find($expiredId));
        $this->assertNotNull($repository->find($active->getId()));
    }

    /**
     * Test méthode findByAccessToken() : session valide, puis refusée si expirée ou inutilisable
     * @return void
     */
    public function testFindByAccessToken(): void
    {
        $tokens = $this->createSessionTokens();
        $this->assertSame(
            $tokens->session->getId(),
            $this->userSessionService->findByAccessToken($tokens->accessToken)?->getId(),
        );
        $this->assertNull($this->userSessionService->findByAccessToken('inconnu'));
        $this->assertNull($this->userSessionService->findByAccessToken($tokens->refreshToken));

        $tokens->session->setAccessTokenExpiresAt(new \DateTimeImmutable('-1 second'));
        $this->persistAndFlush($tokens->session);
        $this->assertNull($this->userSessionService->findByAccessToken($tokens->accessToken));

        $tokens = $this->createSessionTokens();
        $tokens->session->getApiToken()->setDisabled(true);
        $this->persistAndFlush($tokens->session->getApiToken());
        $this->assertNull($this->userSessionService->findByAccessToken($tokens->accessToken));

        $tokens = $this->createSessionTokens();
        $tokens->session->getApiToken()->setExpiresAt(new \DateTime('-1 day'));
        $this->persistAndFlush($tokens->session->getApiToken());
        $this->assertNull($this->userSessionService->findByAccessToken($tokens->accessToken));

        $tokens = $this->createSessionTokens();
        $tokens->session->getUser()->setAnonymous(true);
        $this->persistAndFlush($tokens->session->getUser());
        $this->assertNull($this->userSessionService->findByAccessToken($tokens->accessToken));
    }

    /**
     * Test méthode refresh() : rotation, ancien refresh token réutilisé qui révoque la session
     * @return void
     */
    public function testRefresh(): void
    {
        $first = $this->createSessionTokens();
        $sessionId = $first->session->getId();

        $second = $this->userSessionService->refresh($first->refreshToken);
        $this->assertNotNull($second);
        $this->assertSame($sessionId, $second->session->getId());
        $this->assertNotSame($first->accessToken, $second->accessToken);
        $this->assertNotSame($first->refreshToken, $second->refreshToken);
        $this->assertNull($this->userSessionService->findByAccessToken($first->accessToken));
        $this->assertNotNull($this->userSessionService->findByAccessToken($second->accessToken));

        $this->assertNull($this->userSessionService->refresh($first->refreshToken));
        $this->em->clear();
        $this->assertNull($this->em->getRepository(UserSession::class)->find($sessionId));
        $this->assertNull($this->userSessionService->refresh($second->refreshToken));

        $this->assertNull($this->userSessionService->refresh('inconnu'));
    }

    /**
     * Test méthode refresh() : refresh token expiré ou compte désactivé
     * @return void
     */
    public function testRefreshRefused(): void
    {
        $tokens = $this->createSessionTokens();
        $tokens->session->setRefreshTokenExpiresAt(new \DateTimeImmutable('-1 second'));
        $this->persistAndFlush($tokens->session);
        $this->assertNull($this->userSessionService->refresh($tokens->refreshToken));

        $tokens = $this->createSessionTokens();
        $tokens->session->getApiToken()->setDisabled(true);
        $this->persistAndFlush($tokens->session->getApiToken());
        $this->assertNull($this->userSessionService->refresh($tokens->refreshToken));
    }

    /**
     * Test méthodes revoke(), revokeAll() et findActiveByUser()
     * @return void
     */
    public function testRevoke(): void
    {
        $user = $this->createUserContributeur();
        $first = $this->createUserSession(['user' => $user]);
        $this->createUserSession(['user' => $user]);
        $other = $this->createUserSession();

        $this->assertCount(2, $this->userSessionService->findActiveByUser($user));
        $this->userSessionService->revoke($first);
        $this->assertCount(1, $this->userSessionService->findActiveByUser($user));
        $this->userSessionService->revokeAll($user);
        $this->assertCount(0, $this->userSessionService->findActiveByUser($user));
        $this->assertCount(1, $this->userSessionService->findActiveByUser($other->getUser()));
    }

    /**
     * Test méthode getRefreshTokenLifetime() : valeur de l'option, défaut si la valeur est inconnue
     * @return void
     */
    public function testGetRefreshTokenLifetime(): void
    {
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $key = OptionSystem::OS_API_TIME_VALIDATE_USER_TOKEN->value;

        $optionSystemService->saveValueByKee($key, '43200');
        $this->assertSame(43200, $this->userSessionService->getRefreshTokenLifetime());

        $optionSystemService->saveValueByKee($key, '60');
        $this->assertSame(
            UserSessionService::DEFAULT_REFRESH_TOKEN_LIFETIME,
            $this->userSessionService->getRefreshTokenLifetime(),
        );
    }

    /**
     * Session créée par le service, avec ses tokens en clair
     * @return UserSessionTokens
     */
    private function createSessionTokens(): UserSessionTokens
    {
        return $this->userSessionService->create(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
            $this->createApiToken(['disabled' => false]),
            null,
            null,
        );
    }
}
