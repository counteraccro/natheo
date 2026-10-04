<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service pour les API
 */

namespace App\Service\Api;

use App\Entity\Admin\System\ApiToken;
use App\Entity\Admin\System\User;
use App\Utils\System\ApiToken\TokenHasher;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiService extends AppApiService
{
    /**
     * Intervalle minimum (en secondes) entre deux mises à jour de la date de dernière utilisation d'un token
     * @var int
     */
    private const int LAST_USED_REFRESH_INTERVAL = 60;

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
            'disabled' => 0,
        ]);
        if ($apiToken === null || $apiToken->isExpired()) {
            return null;
        }

        $this->updateLastUsedAt($apiToken);

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
        $lastUsedAt = $apiToken->getLastUsedAt();

        // Évite une écriture en base à chaque appel API
        if ($lastUsedAt !== null && $now->getTimestamp() - $lastUsedAt->getTimestamp() < self::LAST_USED_REFRESH_INTERVAL) {
            return;
        }

        $apiToken->setLastUsedAt($now);
        $this->save($apiToken);
    }
}
