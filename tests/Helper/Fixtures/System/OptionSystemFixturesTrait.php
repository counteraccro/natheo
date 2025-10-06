<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture option user
 */
namespace App\Tests\Helper\Fixtures\System;

use App\Entity\Admin\System\OptionSystem;
use App\Tests\Helper\FakerTrait;
use App\Utils\System\Options\OptionSystemKey;

trait OptionSystemFixturesTrait
{
    use FakerTrait;

    /**
     * Créer une option system
     * @param array $customData
     * @param bool $persist
     * @return OptionSystem
     */
    public function createOptionSystem(array $customData = [], bool $persist = true): OptionSystem
    {
        $data = [
            'key' => self::getFaker()->text(),
            'value' => self::getFaker()->text(),
        ];

        $option = $this->initEntity(OptionSystem::class, array_merge($data, $customData));
        if ($persist) {
            $this->persistAndFlush($option);
        }
        return $option;
    }

    /**
     * Génère les options système par défaut
     * @return void
     */
    public function generateDefaultOptionSystem(): void
    {
        $data = [
            'key' => OptionSystemKey::OS_SITE_NAME,
            'value' => 'Nathéo CMS',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_OPEN_SITE,
            'value' => '1',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_FRONT_SCRIPT_TOP,
            'value' => '',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_FRONT_SCRIPT_START_BODY,
            'value' => '',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_FRONT_SCRIPT_END_BODY,
            'value' => '',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_REPLACE_DELETE_USER,
            'value' => 1,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_CONFIRM_LEAVE_FORM,
            'value' => 1,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_ALLOW_DELETE_DATA,
            'value' => 1,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_DEFAULT_LANGUAGE,
            'value' => 'fr',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_LOGO_SITE,
            'value' => 'bi-yin-yang',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_THEME_SITE,
            'value' => 'purple',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_NB_ELEMENT,
            'value' => 20,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_LOG_DOCTRINE,
            'value' => 1,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_MAIL_FROM,
            'value' => 'support@natheo.fr',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_MAIL_REPLY_TO,
            'value' => 'support@natheo.fr',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_ADRESSE_SITE,
            'value' => 'http://www.value-must-be-change.com',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_MAIL_SIGNATURE,
            'value' => "<hr />Cordialement, <br />L'équipe de Natheo CMS",
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_MAIL_NOTIFICATION,
            'value' => 1,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_PURGE_NOTIFICATION,
            'value' => 30,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_MAIL_RESET_PASSWORD_TIME,
            'value' => 20,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_MEDIA_CREATE_PHYSICAL_FOLDER,
            'value' => 1,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_MEDIA_PATH,
            'value' => 'natheotheque',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_MEDIA_URL,
            'value' => 'http://dev.natheo',
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_API_TIME_VALIDATE_USER_TOKEN,
            'value' => 60,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_OPEN_COMMENT,
            'value' => 1,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_NEW_COMMENT_WAIT_VALIDATION,
            'value' => 1,
        ];

        $data = [
            'key' => OptionSystemKey::OS_NOTIFICATION,
            'value' => 1,
        ];
        $this->createOptionSystem($data);

        $data = [
            'key' => OptionSystemKey::OS_THEME_FRONT_SITE,
            'value' => 'natheo_horizon',
        ];
        $this->createOptionSystem($data);
    }
}
