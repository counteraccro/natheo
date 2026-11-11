<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.3
 * Service gérant les données de l'utilisateur
 */

namespace App\Service\Admin\System\User;

use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserData;
use App\Service\Admin\AppAdminService;
use App\Enum\Admin\System\User\UserDataKey;
use DateTime;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\String\ByteString;

class UserDataService extends AppAdminService
{
    /**
     * Met à jour une dataUser avec value en fonction de sa clé et du user
     * @param string $key
     * @param mixed $value
     * @param User $user
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update(string $key, string $value, User $user): void
    {
        $userData = $this->findKeyAndUser($key, $user);
        if ($userData === null) {
            $userData = new UserData();
            $userData->setKey($key)->setValue($value);
            $user->addUserData($userData);
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findKeyAndValue(string $key, string $value): ?UserData
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
        $userData = $user->getUserDataByKey(UserDataKey::LAST_CONNEXION->value);

        if ($userData === null) {
            return null;
        }

        $lastConnexion = new DateTime();
        $lastConnexion->setTimestamp(intval($userData->getValue()));
        return $lastConnexion;
    }

    /**
     * Retourne true ou false pour l'aide
     * (Clé KEY_HELP_FIRST_CONNEXION)
     * @param User $user
     * @return bool
     */
    public function getHelpFirstConnexion(User $user): bool
    {
        $userData = $user->getUserDataByKey(UserDataKey::HELP_FIRST_CONNEXION->value);

        if ($userData === null) {
            return false;
        }

        if (intval($userData->getValue()) === 1) {
            return true;
        }
        return false;
    }

    /**
     * Génère une clé de réinitialisation du mot de passe pour le user.
     * Seul le hash de la clé est stocké, la clé en clair est retournée pour être envoyée par mail
     * @param User $user
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function generateResetPasswordKey(User $user): string
    {
        $key = ByteString::fromRandom(48)->toString();
        $this->update(UserDataKey::RESET_PASSWORD->value, self::hashResetPasswordKey($key), $user);
        return $key;
    }

    /**
     * Retourne le hash d'une clé de réinitialisation du mot de passe
     * @param string $key
     * @return string
     */
    public static function hashResetPasswordKey(string $key): string
    {
        return hash('sha256', $key);
    }

    /**
     * Supprime la clé de réinitialisation du mot de passe du user
     * @param User $user
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function removeResetPasswordKey(User $user): void
    {
        $userData = $this->findKeyAndUser(UserDataKey::RESET_PASSWORD->value, $user);
        if ($userData !== null) {
            $user->removeUserData($userData);
            $this->remove($userData);
        }
    }
}
