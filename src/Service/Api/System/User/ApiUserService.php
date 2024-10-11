<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service pour les User pour les API
 */
namespace App\Service\Api\System\User;

use App\Entity\Admin\System\User;
use App\Service\Api\AppApiService;

class ApiUserService extends AppApiService
{
    /**
     * Retourne un utilisateur en fonction de son token si celui ci est valide
     * @param string $userToken
     * @return User|null
     */
    public function getUserByUserToken(string $userToken) :?User
    {
        echo "oki";
        return null;
    }
}
