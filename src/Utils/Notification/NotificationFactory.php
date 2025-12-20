<?php
/**
 * Création de notifications
 * @author Gourdon Aymeric
 * @version 2.0
 */

namespace App\Utils\Notification;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Global\Notification\KeyConfig;
use App\Enum\Admin\Global\Notification\Notification as NotificationEnum;

class NotificationFactory
{
    public function __construct(private User $user) {}

    /**
     * Créer une entité Notification
     * @return Notification
     */
    private function createNotification(): Notification
    {
        $notification = new Notification();
        $notification->setUser($this->user);
        $notification->setRead(false);

        $this->user->addNotification($notification);

        return $notification;
    }

    /**
     * Retourne le user avec les notifications associées
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Création notification
     * @param string $key
     * @param array $params
     * @return NotificationFactory
     */
    public function addNotification(string $key, array $params): NotificationFactory
    {
        if (!NotificationEnum::isExist($key)) {
            return $this;
        }
        $tabNotif = NotificationEnum::getNotification($key);

        $tabParameter = $tabNotif[KeyConfig::PARAMETERS->value];
        foreach ($params as $key => $value) {
            if (isset($tabParameter[$key])) {
                $tabParameter[$key] = $value;
            }
        }

        $notification = $this->createNotification();
        $notification->setTitle($tabNotif[KeyConfig::TITLE->value]);
        $notification->setContent($tabNotif[KeyConfig::CONTENT->value]);
        $notification->setLevel($tabNotif[KeyConfig::LEVEL->value]);
        $notification->setCategory($tabNotif[KeyConfig::CATEGORY->value]);
        $notification->setParameters(json_encode($tabParameter));

        return $this;
    }
}
