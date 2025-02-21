<?php

namespace App\Tests\Helper\Fixtures\System\User;

use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserData;
use App\Tests\Helper\FakerTrait;

trait UserDataFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'une UserData
     * @param User|null $user
     * @param array $customData
     * @param bool $persist
     * @return UserData
     */
    public function createUserData(User $user = null,  array $customData = [], bool $persist = true): UserData
    {
        if($user === null) {
            $user = $this->createUser();
        }
        $data = [
            'user' => $user,
            'key' => self::getFaker()->text(),
            'value' => self::getFaker()->text(),
        ];

        $option = $this->initEntity(UserData::class, array_merge($data, $customData));
        $user->addUserData($option);
        if ($persist) {
            $this->persistAndFlush($option);
        }
        return $option;
    }
}