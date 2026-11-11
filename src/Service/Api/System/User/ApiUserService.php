<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service pour les User pour les API
 */
namespace App\Service\Api\System\User;

use App\Entity\Admin\System\User;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiUserService extends AppApiService
{
    /**
     * Retourne l'utilisateur d'un access token de session valide (compte actif, token d'API actif)
     * @param string $userToken
     * @return User|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserByUserToken(string $userToken): ?User
    {
        return $this->getUserSessionService()->findByAccessToken($userToken)?->getUser();
    }
}
