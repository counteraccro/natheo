<?php

declare(strict_types=1);
namespace App\Security\Provider;

use App\Entity\Admin\System\ApiToken;
use App\Service\Api\ApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiProvider implements UserProviderInterface
{
    public function __construct(private ApiService $apiService) {}

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
        $user = $this->apiService->getUserByApiToken($identifier);
        if ($user === null) {
            throw new UserNotFoundException('API Key is not correct');
        }
        return $user;
    }

    /**
     * Jamais appelé : le firewall API est stateless, l'utilisateur n'est pas rechargé depuis la session
     * @param UserInterface $user
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new UnsupportedUserException('Stateless API firewall, refresh is not supported');
    }

    /**
     * Tells Symfony to use this provider for this class.
     */
    public function supportsClass(string $class): bool
    {
        return ApiToken::class === $class || is_subclass_of($class, ApiToken::class);
    }
}
