<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Service pour les API
 */

namespace App\Service\Api;

use App\Entity\Admin\System\ApiToken;
use App\Entity\Admin\System\User;
use App\Enum\Admin\System\Options\OptionSystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use App\Service\Global\Token\TokenHasher;

class ApiService extends AppApiService
{
    /**
     * Attribut de requête : token d'API de l'appel (celui de la session pour un access token utilisateur)
     * @var string
     */
    public const string ATTRIBUTE_API_TOKEN = '_api_token';

    /**
     * Attribut de requête : session utilisateur de l'appel, absent pour un token d'API seul
     * @var string
     */
    public const string ATTRIBUTE_USER_SESSION = '_api_user_session';

    /**
     * Détermine si on peut accéder à l'API ou non
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function canAccessToApi(): bool
    {
        $ipClient = $this->getRequestStack()->getCurrentRequest()->getClientIp();
        $ip = $this->getParameterBag()->get('app.ip_api_authorize');
        $filter = $this->getParameterBag()->get('app.ip_api_active_filter');

        if (!$filter) {
            return true;
        }

        if (!in_array($ipClient, $ip)) {
            return false;
        }
        return true;
    }

    /**
     * Indique si l'API est ouverte (option OS_OPEN_API)
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isApiOpen(): bool
    {
        // Option absente (installation antérieure) : on applique la valeur par défaut
        $isOpenApi =
            $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_OPEN_API->value) ??
            OptionSystem::OS_OPEN_API->getDefault();
        return intval($isOpenApi) === 1;
    }

    /**
     * Converti un objet ApiToken en User pour le ApiProvider Si les conditions suivantes sont remplis
     *  - IP autorisée, Token valide, actif et non expiré
     * @param string $token
     * @return User|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserByApiToken(string $token): ?User
    {
        if (!$this->canAccessToApi()) {
            return null;
        }

        /** @var ApiToken $apiToken */
        $apiToken = $this->findOneByCriteria(ApiToken::class, [
            'token' => TokenHasher::hash($token),
            'disabled' => false,
        ]);
        if ($apiToken === null || $apiToken->isExpired()) {
            return null;
        }

        $this->updateLastUsedAt($apiToken);
        $this->getRequestStack()->getCurrentRequest()?->attributes->set(self::ATTRIBUTE_API_TOKEN, $apiToken);
        return $this->createSecurityUser($apiToken);
    }

    /**
     * Converti l'access token d'une session utilisateur en User pour le ApiProvider, avec les rôles du token d'API
     * de la session, si l'IP est autorisée et la session valide
     * @param string $token
     * @return User|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserBySessionToken(string $token): ?User
    {
        if (!$this->canAccessToApi()) {
            return null;
        }

        $session = $this->getUserSessionService()->findByAccessToken($token);
        if ($session === null) {
            return null;
        }

        $attributes = $this->getRequestStack()->getCurrentRequest()?->attributes;
        $attributes?->set(self::ATTRIBUTE_API_TOKEN, $session->getApiToken());
        $attributes?->set(self::ATTRIBUTE_USER_SESSION, $session);
        return $this->createSecurityUser($session->getApiToken());
    }

    /**
     * User de sécurité du firewall API, qui porte les rôles du token d'API
     * @param ApiToken $apiToken
     * @return User
     */
    private function createSecurityUser(ApiToken $apiToken): User
    {
        $user = new User();
        $user->setRoles($apiToken->getRoles());
        $user->setUpdateAt(new \DateTime());
        return $user;
    }

    /**
     * Met à jour la date de dernière utilisation du token
     * @param ApiToken $apiToken
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function updateLastUsedAt(ApiToken $apiToken): void
    {
        $now = new \DateTime();
        if (!$apiToken->isLastUsedAtOutdated($now)) {
            return;
        }

        $apiToken->setLastUsedAt($now);
        $this->save($apiToken);
    }
}
