<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Utilisateur de sécurité du pare-feu API : token d'API de l'appel et, pour un access token, session de l'utilisateur
 */

namespace App\Security;

use App\Entity\Admin\System\ApiToken;
use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserSession;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class ApiUser implements UserInterface
{
    /**
     * Rôle d'un appel fait avec l'access token d'une session utilisateur
     * @var string
     */
    public const string ROLE_USER_SESSION = 'ROLE_API_USER_SESSION';

    /**
     * @param ApiToken $apiToken token d'API de l'appel, celui de la session pour un access token
     * @param UserSession|null $session session de l'utilisateur, null pour un token d'API seul
     */
    public function __construct(private ApiToken $apiToken, private ?UserSession $session = null) {}

    public function getApiToken(): ApiToken
    {
        return $this->apiToken;
    }

    public function getSession(): ?UserSession
    {
        return $this->session;
    }

    /**
     * Utilisateur réel de la session, null pour un token d'API seul
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->session?->getUser();
    }

    /**
     * Rôles du token, rôle de son type et, avec une session, rôles de l'utilisateur
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = [...$this->apiToken->getRoles(), $this->apiToken->getScope()->getRole()];
        $user = $this->getUser();
        if ($user !== null) {
            $roles = [...$roles, self::ROLE_USER_SESSION, ...$user->getRoles()];
        }
        return array_values(array_unique($roles));
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->getUser()?->getUserIdentifier() ?? 'api_token_' . $this->apiToken->getId();
    }
}
