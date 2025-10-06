<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture option user
 */
namespace App\Tests\Helper\Fixtures\System\User;

use App\Entity\Admin\System\OptionUser;
use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;
use App\Utils\System\Options\OptionUserKey;

trait OptionUserFixturesTrait
{
    use FakerTrait;

    /**
     * Créer une option user
     * @param User|null $user
     * @param array $customData
     * @param bool $persist
     * @return OptionUser
     */
    public function createOptionUser(?User $user = null, array $customData = [], bool $persist = true): OptionUser
    {
        if ($user === null) {
            $user = $this->createUser();
        }
        $data = [
            'user' => $user,
            'key' => self::getFaker()->text(),
            'value' => self::getFaker()->text(),
        ];

        $option = $this->initEntity(OptionUser::class, array_merge($data, $customData));
        $user->addOptionsUser($option);
        if ($persist) {
            $this->persistAndFlush($option);
        }
        return $option;
    }

    /**
     * Génère les options par défauts
     * @param User $user
     * @return void
     */
    public function generateDefaultOptionUser(User $user): void
    {
        $data = [
            'key' => OptionUserKey::OU_THEME_SITE,
            'value' => 'purple',
        ];
        $this->createOptionUser($user, $data);

        $data = [
            'key' => OptionUserKey::OU_DEFAULT_LANGUAGE,
            'value' => 'fr',
        ];
        $this->createOptionUser($user, $data);

        $data = [
            'key' => OptionUserKey::OU_NB_ELEMENT,
            'value' => '20',
        ];
        $this->createOptionUser($user, $data);

        $data = [
            'key' => OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER,
            'value' => 'email',
        ];
        $this->createOptionUser($user, $data);
    }
}
