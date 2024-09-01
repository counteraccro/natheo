<?php
/**
 * Création de notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Notification;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\User;

class NotificationFactory
{
    public function __construct(
        private User $user
    )
    {

    }

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

        if (!isset(NotificationKey::TAB_NOTIFICATIONS[$key])) {
            return $this;
        }
        $tabNotif = NotificationKey::TAB_NOTIFICATIONS[$key];

        $tabParameter = $tabNotif[NotificationKey::KEY_PARAMETER];
        foreach ($params as $key => $value) {
            if (isset($tabParameter[$key])) {
                $tabParameter[$key] = $value;
            }
        }

        $notification = $this->createNotification();
        $notification->setTitle($tabNotif[NotificationKey::KEY_TITLE]);
        $notification->setContent($tabNotif[NotificationKey::KEY_CONTENT]);
        $notification->setLevel($tabNotif[NotificationKey::KEY_LEVEL]);
        $notification->setParameters(json_encode($tabParameter));

        return $this;
    }
}
