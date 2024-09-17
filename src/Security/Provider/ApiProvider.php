<?php
namespace App\Security\Provider;

use App\Entity\Admin\System\ApiToken;
use App\Service\Api\ApiTokenService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiProvider implements UserProviderInterface
{
    public function __construct(private ApiTokenService $apiTokenService)
    {
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me. If you're not using these features, you do not
     * need to implement this method.
     *
     * @param string $identifier
     * @return UserInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->apiTokenService->getUserByApiToken($identifier);
        if($user === null)
        {
            throw new UserNotFoundException('API Key is not correct');
        }
        return $user;
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new \Exception('TODO: fill in refreshUser() inside ' . __FILE__);
    }

    /**
     * Tells Symfony to use this provider for this class.
     */
    public function supportsClass(string $class): bool
    {
        return ApiToken::class === $class || is_subclass_of($class, ApiToken::class);
    }
}
