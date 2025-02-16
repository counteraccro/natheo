<?php

namespace App\Tests\Helper\Fixtures;

use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;

trait UserFixturesTrait
{
    use FakerTrait;

    /**
     * Créer un utilisateur de role USER
     * @param array $customData
     * @param bool $persist
     * @return User
     */
    public function createUser(array $customData = [], bool $persist = true) :User
    {
        $data = [
            'email' => self::getFaker()->email(),
            'roles' => ['ROLE_USER'],
            'password' => self::getFaker()->password(),
            'login' => self::getFaker()->username(),
            'firstname' => self::getFaker()->firstName(),
            'lastname' => self::getFaker()->lastName(),
            'disabled' => self::getFaker()->boolean(),
            'anonymous' => self::getFaker()->boolean(),
            'founder' => self::getFaker()->boolean(),
        ];

        $user = $this->initEntity(User::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($user);
        }
        return $user;
    }

}