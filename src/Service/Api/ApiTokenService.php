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
     * Converti un objet ApiToken en User pour le ApiProvider
     * @param string $token
     * @return User|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserByApiToken(string $token): ?User
    {
        /** @var ApiToken $apiToken */
        $apiToken = $this->findOneBy(ApiToken::class, 'token', $token);
        if($apiToken === null)
        {
            return null;
        }

        $user = new User();
        $user->setRoles($apiToken->getRoles());
        $user->setUpdateAt(new \DateTime());
        return $user;
    }
}
