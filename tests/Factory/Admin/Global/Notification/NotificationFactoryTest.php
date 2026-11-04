<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test NotificationFactory
 */
namespace App\Tests\Factory\Admin\Global\Notification;

use App\Entity\Admin\System\User;
use App\Enum\Admin\Global\Notification\Notification;
use App\Tests\AppWebTestCase;
use App\Factory\Admin\Global\Notification\NotificationFactory;

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
        $factory->addNotification(Notification::SELF_DELETE->value, ['login' => $user->getLogin()]);

        $user = $factory->getUser();
        $this->assertInstanceOf(User::class, $user);
        $this->assertCount(1, $user->getNotifications());
    }
}
