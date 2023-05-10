<?php
/**
 * Service pour les notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Service\Admin;

use App\Entity\Admin\User;
use App\Utils\Notification\NotificationFactory;

class NotificationService extends AppAdminService
{

    /**
     * Permet d'ajouter une notification
     * @param User $user
     * @param string $key
     * @param array $params
     * @return void
     */
    public function add(User $user, string $key, array $params): void
    {
        $notificationFactory = new NotificationFactory($user);
        $user = $notificationFactory->addNotification($key, $params)->getUser();
        $this->save($user);
    }

    /**
     * @param User $user
     * @param string $key
     * @param array $params
     * @return User
     */
    public function addForFixture(User $user, string $key, array $params): User
    {
        $notificationFactory = new NotificationFactory($user);
        return $notificationFactory->addNotification($key, $params)->getUser();
    }

}
