<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Création fixture notification
 */

namespace App\Tests\Helper\Fixtures\System;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;

trait NotificationFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'une notification random
     * @param User $user
     * @param array $customData
     * @param bool $persist
     * @return Notification
     */
    public function createNotification(User $user, array $customData = [], bool $persist = true): Notification
    {
        $data = [
            'user' => $user,
            'title' => self::getFaker()->text(),
            'content' => self::getFaker()->text(),
            'level' => self::getFaker()->numberBetween(1, 5),
            'read' =>   self::getFaker()->boolean(),
            'parameters' => json_encode([])
        ];
        $notification = $this->initEntity(Notification::class, array_merge($data, $customData));
        $user->addNotification($notification);

        if ($persist) {
            $this->persistAndFlush($notification);
        }
        return $notification;
    }
}