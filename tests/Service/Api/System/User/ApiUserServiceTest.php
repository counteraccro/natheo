<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test Service ApiUserService
 */

namespace Service\Api\System\User;

use App\Entity\Admin\System\User;
use App\Service\Api\System\User\ApiUserService;
use App\Tests\AppWebTestCase;
use App\Enum\Admin\System\User\UserDataKey;
use App\Utils\System\ApiToken\TokenHasher;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

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
     * Test méthode getUserByUserToken()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetUserByUserToken(): void
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $this->createUserTokenData($user, 'azerty-token', 'P10D', false);
        $this->assertInstanceOf(User::class, $this->apiUserService->getUserByUserToken('azerty-token'));
        // Le hash stocké ne doit pas permettre de s'authentifier
        $this->assertNull($this->apiUserService->getUserByUserToken(TokenHasher::hash('azerty-token')));

        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $this->createUserTokenData($user, 'azerty-token-2', 'P10D', true);
        $this->assertNull($this->apiUserService->getUserByUserToken('azerty-token-2'));

        $user = $this->createUserContributeur(['disabled' => true, 'anonymous' => false]);
        $this->createUserTokenData($user, 'azerty-token-3', 'P10D', false);
        $this->assertNull($this->apiUserService->getUserByUserToken('azerty-token-3'));

        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $this->createUserData($user, [
            'key' => UserDataKey::TOKEN_CONNEXION->value,
            'value' => TokenHasher::hash('azerty-token-4'),
        ]);
        $this->assertNull($this->apiUserService->getUserByUserToken('azerty-token-4'));
    }

    /**
     * Crée le token utilisateur (hashé) et sa date de validité
     * @param User $user
     * @param string $token
     * @param string $interval
     * @param bool $expired
     * @return void
     */
    private function createUserTokenData(User $user, string $token, string $interval, bool $expired): void
    {
        $this->createUserData($user, [
            'key' => UserDataKey::TOKEN_CONNEXION->value,
            'value' => TokenHasher::hash($token),
        ]);
        $date = new \DateTime();
        $expired ? $date->sub(new \DateInterval($interval)) : $date->add(new \DateInterval($interval));
        $this->createUserData($user, [
            'key' => UserDataKey::TIME_VALIDATE_TOKEN->value,
            'value' => strval($date->getTimestamp()),
        ]);
    }
}
