<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Test Service ApiUserService
 */

namespace Service\Api\System\User;

use App\Entity\Admin\System\User;
use App\Service\Api\System\User\ApiUserService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use App\Service\Global\Token\TokenHasher;

class ApiUserServiceTest extends AppWebTestCase
{
    /**
     * @var mixed|ApiUserService|Container|null
     */
    private ApiUserService $apiUserService;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiUserService = $this->container->get(ApiUserService::class);
    }

    /**
     * Test méthode getUserByUserToken() : access token d'une session valide uniquement
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetUserByUserToken(): void
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $this->createSession($user, 'azerty-token');
        $this->assertSame($user->getId(), $this->apiUserService->getUserByUserToken('azerty-token')?->getId());
        // Le hash stocké ne doit pas permettre de s'authentifier
        $this->assertNull($this->apiUserService->getUserByUserToken(TokenHasher::hash('azerty-token')));

        $this->createSession(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
            'azerty-token-2',
            new \DateTimeImmutable('-1 second'),
        );
        $this->assertNull($this->apiUserService->getUserByUserToken('azerty-token-2'));

        $this->createSession(
            $this->createUserContributeur(['disabled' => true, 'anonymous' => false]),
            'azerty-token-3',
        );
        $this->assertNull($this->apiUserService->getUserByUserToken('azerty-token-3'));
    }

    /**
     * Crée une session dont l'access token est connu
     * @param User $user
     * @param string $accessToken
     * @param \DateTimeImmutable|null $expiresAt
     * @return void
     */
    private function createSession(User $user, string $accessToken, ?\DateTimeImmutable $expiresAt = null): void
    {
        $this->createUserSession([
            'user' => $user,
            'apiToken' => $this->createApiToken(['disabled' => false]),
            'accessTokenHash' => TokenHasher::hash($accessToken),
            'accessTokenExpiresAt' => $expiresAt ?? new \DateTimeImmutable('+15 minutes'),
        ]);
    }
}
