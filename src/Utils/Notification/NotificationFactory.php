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
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
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
    public function getUser()
    {
        return $this->user;
    }


    /**
     * Création notification bienvenue
     * @param string $key
     * @param array $params
     * @return NotificationFactory
     */
    public function addNotification(string $key, array $params) : NotificationFactory
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
        /*$parameter = implode('|', array_map(
            function ($v, $k) {
                return sprintf("%s:%s", $k, $v);
            },
            $tab,
            array_keys($tab)));*/

        $notification = $this->createNotification();
        $notification->setTitle($tabNotif[NotificationKey::KEY_TITLE]);
        $notification->setContent($tabNotif[NotificationKey::KEY_CONTENT]);
        $notification->setLevel($tabNotif[NotificationKey::KEY_LEVEL]);
        $notification->setParameters(json_encode($tabParameter));

        return $this;
    }
}
