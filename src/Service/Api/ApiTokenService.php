<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service pour ApiToken
 */

namespace App\Service\Api;

use App\Entity\Admin\System\User;
use App\Entity\Api\ApiToken;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiTokenService extends AppApiService
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
     * Converti un objet ApiToken en User pour le ApiProvider Si les conditions suivantes sont remplis
     *  - IP autorisée, Token valide
     * @param string $token
     * @return User|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserByApiToken(string $token): ?User
    {
        if(!$this->canAccessToApi()) {
            return null;
        }

        /** @var ApiToken $apiToken */
        $apiToken = $this->findOneBy(ApiToken::class, 'token', $token);
        if ($apiToken === null) {
            return null;
        }

        $user = new User();
        $user->setRoles($apiToken->getRoles());
        $user->setUpdateAt(new \DateTime());
        return $user;
    }
}