<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture text user
 */

namespace App\Tests\Helper\Fixtures\System\User;

use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait UserFixturesTrait
{
    use FakerTrait;

    /**
     * Créer un utilisateur de role USER
     * @param array $customData
     * @param bool $persist
     * @return User
     */
    public function createUser(array $customData = [], bool $persist = true): User
    {
        /** @var UserPasswordHasherInterface $userPasswordHasher */
        $userPasswordHasher = $this->container->get(UserPasswordHasherInterface::class);

        $data = [
            'email' => self::getFaker()->email(),
            'roles' => ['ROLE_USER'],
            'password' => self::getFaker()->password(),
            'login' => self::getFaker()->username(),
            'firstname' => self::getFaker()->firstName(),
            'lastname' => self::getFaker()->lastName(),
            'disabled' => self::getFaker()->boolean(),
            'anonymous' => self::getFaker()->boolean(),
            'founder' => false,
        ];

        $user = $this->initEntity(User::class, array_merge($data, $customData));
        $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->generateDefaultOptionUser($user);

        if ($persist) {
            $this->persistAndFlush($user);
        }
        return $user;
    }

    /**
     * Créer un utilisateur de role Contributeur
     * @param array $customData
     * @param bool $persist
     * @return User
     */
    public function createUserContributeur(array $customData = [], bool $persist = true): User
    {
        $data = [
            'roles' => ['ROLE_CONTRIBUTEUR'],
        ];

        $user = $this->createUser(array_merge($data, $customData), false);
        if ($persist) {
            $this->persistAndFlush($user);
        }
        return $user;
    }

    /**
     * Créer un utilisateur de role admin
     * @param array $customData
     * @param bool $persist
     * @return User
     */
    public function createUserAdmin(array $customData = [], bool $persist = true): User
    {
        $data = [
            'roles' => ['ROLE_ADMIN'],
        ];

        $user = $this->createUser(array_merge($data, $customData), false);
        if ($persist) {
            $this->persistAndFlush($user);
        }
        return $user;
    }

    /**
     * Créer un utilisateur de role superAdmin
     * @param array $customData
     * @param bool $persist
     * @return User
     */
    public function createUserSuperAdmin(array $customData = [], bool $persist = true): User
    {
        $data = [
            'roles' => ['ROLE_SUPER_ADMIN'],
        ];

        $user = $this->createUser(array_merge($data, $customData), false);
        if ($persist) {
            $this->persistAndFlush($user);
        }
        return $user;
    }

    /**
     * Créer un user fondateur
     * @param array $customData
     * @param bool $persist
     * @return User
     */
    public function createUserFounder(array $customData = [], bool $persist = true): User
    {
        $data = [
            'roles' => ['ROLE_SUPER_ADMIN'],
            'founder' => true,
        ];

        $user = $this->createUser(array_merge($data, $customData), false);

        if ($persist) {
            $this->persistAndFlush($user);
        }
        return $user;
    }
}