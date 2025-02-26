<?php

namespace App\Tests\Utils\Notification;

use App\Entity\Admin\System\User;
use App\Tests\AppWebTestCase;
use App\Utils\Notification\NotificationFactory;
use App\Utils\Notification\NotificationKey;

class NotificationFactoryTest extends AppWebTestCase
{
    /**
     * Test création notification
     * @return void
     */
    public function testAddNotification()
    {
        $user = $this->createUser();
        $factory = new NotificationFactory($user);
        $factory->addNotification(NotificationKey::NOTIFICATION_SELF_DELETE, ['login' => $user->getLogin()]);

        $user = $factory->getUser();
        $this->assertInstanceOf(User::class, $user);
        $this->assertCount(1, $user->getNotifications());
    }
}