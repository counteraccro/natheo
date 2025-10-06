<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour les User pour les API
 */
namespace App\Service\Api\System\User;

use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserData;
use App\Repository\Admin\System\UserRepository;
use App\Service\Api\AppApiService;
use App\Utils\System\User\UserDataKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiUserService extends AppApiService
{
    /**
     * Retourne un utilisateur en fonction de son token si celui ci est valide
     * @param string $userToken
     * @return User|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getUserByUserToken(string $userToken): ?User
    {
        $repository = $this->getRepository(UserData::class);
        $userData = $repository->findByKeyValue(UserDataKey::KEY_TOKEN_CONNEXION, $userToken);
        if (is_null($userData)) {
            return null;
        }
        /** @var User $user */
        $user = $userData->getUser();
        $time = intval($user->getUserDataByKey(UserDataKey::TIME_VALIDATE_TOKEN)->getValue());

        // Si le token n'est pas périmé
        if ($time > time()) {
            return $user;
        }
        return null;
    }
}
