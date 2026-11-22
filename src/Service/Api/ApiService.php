<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Service pour les API
 */

namespace App\Service\Api;

use App\Entity\Admin\System\ApiToken;
use App\Security\ApiUser;
use App\Enum\Admin\System\Options\OptionSystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use App\Service\Global\Token\TokenHasher;

class ApiService extends AppApiService
{
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
     * Convertit un token d'API en ApiUser pour le ApiProvider, si l'IP est autorisée et le token actif et non expiré
     * @param string $token
     * @return ApiUser|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserByApiToken(string $token): ?ApiUser
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
        return new ApiUser($apiToken);
    }

    /**
     * Convertit l'access token d'une session utilisateur en ApiUser pour le ApiProvider, avec les rôles du token d'API
     * de la session et ceux de l'utilisateur, si l'IP est autorisée et la session valide
     * @param string $token
     * @return ApiUser|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserBySessionToken(string $token): ?ApiUser
    {
        if (!$this->canAccessToApi()) {
            return null;
        }

        $session = $this->getUserSessionService()->findByAccessToken($token);
        if ($session === null) {
            return null;
        }
        return new ApiUser($session->getApiToken(), $session);
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
