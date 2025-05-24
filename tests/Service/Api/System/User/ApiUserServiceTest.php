<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test Service ApiUserService
 */

namespace Service\Api\System\User;

use App\Entity\Admin\System\User;
use App\Service\Api\System\User\ApiUserService;
use App\Tests\AppWebTestCase;
use App\Utils\System\User\UserDataKey;
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
        $user = $this->createUserContributeur();
        $userData = $this->createUserData($user, ['key' => UserDataKey::KEY_TOKEN_CONNEXION, 'value' => 'azerty-token']);
        $this->createUserData($user, ['key' => UserDataKey::TIME_VALIDATE_TOKEN, 'value' => (new \DateTime())->add(new \DateInterval('P10D'))->getTimestamp()]);
        $user = $this->apiUserService->getUserByUserToken($userData->getValue());
        $this->assertInstanceOf(User::class, $user);

        $user = $this->createUserContributeur();
        $userData = $this->createUserData($user, ['key' => UserDataKey::KEY_TOKEN_CONNEXION, 'value' => 'azerty-token-2']);
        $this->createUserData($user, ['key' => UserDataKey::TIME_VALIDATE_TOKEN, 'value' => (new \DateTime())->sub(new \DateInterval('P10D'))->getTimestamp()]);
        $user = $this->apiUserService->getUserByUserToken($userData->getValue());
        $this->assertNull($user);
    }
}