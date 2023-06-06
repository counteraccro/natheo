<?php

namespace App\Service\Admin\User;

use App\Entity\Admin\User;
use App\Entity\Admin\UserData;
use App\Service\Admin\AppAdminService;
use App\Utils\User\UserdataKey;
use DateTime;

class UserDataService extends AppAdminService
{

    /**
     * Met à jour une dataUser avec value en fonction de sa clé et du user
     * @param string $key
     * @param mixed $value
     * @param User $user
     * @return void
     */
    public function update(string $key, mixed $value, User $user): void
    {
        $userData = $this->findKeyAndUser($key, $user);
        if ($userData === null) {
            $userData = new UserData();
            $userData->setUser($user)->setKey($key)->setValue($value);
        } else {
            $userData->setValue($value);
        }
        $this->save($userData);
    }

    /**
     * Recherche une dataUser en fonction de sa clé et du user
     * @param string $key
     * @param User $user
     * @return UserData|null
     */
    public function findKeyAndUser(string $key, User $user): ?UserData
    {
        $repo = $this->getRepository(UserData::class);
        return $repo->findOneBy(['key' => $key, 'user' => $user]);
    }

    /**
     * Renvoi une UserData en fonction de sa clé et sa valeur
     * @param string $key
     * @param string $value
     * @return UserData|null
     */
    public function findKeyAndValue(string $key, string $value) :?UserData
    {
        $repo = $this->getRepository(UserData::class);
        return $repo->findByKeyValue($key, $value);
    }

    /**
     * Retourne la dernière date de connexion
     *  (cle KEY_LAST_CONNEXION)
     * @param User $user
     * @return DateTime|null
     */
    public function getLastConnexion(User $user): ?DateTime
    {
        $userData = $user->getUserDataByKey(UserdataKey::KEY_LAST_CONNEXION);

        if ($userData === null) {
            return null;
        }

        $lastConnexion = new DateTime();
        $lastConnexion->setTimestamp($userData->getValue());
        return $lastConnexion;
    }
}
