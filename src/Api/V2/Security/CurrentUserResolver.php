<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Utilisateur du back-office de la requête, authentifié par l'access token de sa session
 */

namespace App\Api\V2\Security;

use App\Api\V2\Exception\ForbiddenException;
use App\Entity\Admin\System\ApiToken;
use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserSession;
use App\Service\Api\ApiService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CurrentUserResolver
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {}

    /**
     * Session de la requête, null pour un appel fait avec un token d'API seul
     * @return UserSession|null
     */
    public function getSession(): ?UserSession
    {
        $session = $this->requestStack->getCurrentRequest()?->attributes->get(ApiService::ATTRIBUTE_USER_SESSION);
        return $session instanceof UserSession ? $session : null;
    }

    /**
     * Session de la requête, pour une opération réservée à un access token de session
     * @return UserSession
     * @throws ForbiddenException pour un appel fait avec un token d'API seul
     */
    public function requireSession(): UserSession
    {
        return $this->getSession() ??
            throw new ForbiddenException($this->translator->trans('api_errors.session.required', domain: 'api_errors'));
    }

    /**
     * Utilisateur de la session de la requête, null pour un appel fait avec un token d'API seul
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->getSession()?->getUser();
    }

    /**
     * Token d'API de la requête, celui de la session pour un access token utilisateur
     * @return ApiToken|null
     */
    public function getApiToken(): ?ApiToken
    {
        $apiToken = $this->requestStack->getCurrentRequest()?->attributes->get(ApiService::ATTRIBUTE_API_TOKEN);
        return $apiToken instanceof ApiToken ? $apiToken : null;
    }
}
