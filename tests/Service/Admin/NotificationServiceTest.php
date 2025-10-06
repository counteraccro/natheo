<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test service notification
 */

namespace App\Tests\Service\Admin;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\User;
use App\Service\Admin\NotificationService;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use App\Utils\Notification\NotificationKey;
use App\Utils\System\Options\OptionSystemKey;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotificationServiceTest extends AppWebTestCase
{
    /**
     * @var NotificationService
     */
    private NotificationService $notificationService;

    public function setUp(): void
    {
        parent::setUp();
        $this->notificationService = $this->container->get(NotificationService::class);
    }

    /**
     * Test méthode add
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAdd(): void
    {
        $user = $this->createUser();

        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '0');

        $this->notificationService->add($user, NotificationKey::NOTIFICATION_SELF_DELETE, [
            'login' => $user->getLogin(),
        ]);

        $user = $this->notificationService->findOneById(User::class, $user->getId());
        $this->assertCount(0, $user->getNotifications());

        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '1');

        $this->notificationService->add($user, NotificationKey::NOTIFICATION_SELF_DELETE, [
            'login' => $user->getLogin(),
        ]);
        $user = $this->notificationService->findOneById(User::class, $user->getId());
        $this->assertCount(1, $user->getNotifications());
        $notification = $user->getNotifications()->first();

        $tab = NotificationKey::TAB_NOTIFICATIONS[NotificationKey::NOTIFICATION_SELF_DELETE];
        $this->assertEquals($tab[NotificationKey::KEY_TITLE], $notification->getTitle());
        $this->assertEquals($tab[NotificationKey::KEY_CONTENT], $notification->getContent());
        $this->assertEquals($tab[NotificationKey::KEY_LEVEL], $notification->getLevel());
    }

    /**
     * Test méthode addForFixture()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddForFixture(): void
    {
        $user = $this->createUser();

        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '0');
        $user = $this->notificationService->addForFixture($user, NotificationKey::NOTIFICATION_SELF_DISABLED, [
            'login' => $user->getLogin(),
        ]);
        $this->assertCount(0, $user->getNotifications());

        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '1');
        $user = $this->notificationService->addForFixture($user, NotificationKey::NOTIFICATION_SELF_DISABLED, [
            'login' => $user->getLogin(),
        ]);
        $this->assertCount(1, $user->getNotifications());

        $notification = $user->getNotifications()->first();

        $tab = NotificationKey::TAB_NOTIFICATIONS[NotificationKey::NOTIFICATION_SELF_DISABLED];
        $this->assertEquals($tab[NotificationKey::KEY_TITLE], $notification->getTitle());
        $this->assertEquals($tab[NotificationKey::KEY_CONTENT], $notification->getContent());
        $this->assertEquals($tab[NotificationKey::KEY_LEVEL], $notification->getLevel());
    }

    /**
     * Test méthode getNbByUser()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function testGetNbByUser(): void
    {
        $user = $this->createUser();

        for ($i = 0; $i < 10; $i++) {
            $data = ['read' => 0];
            $this->createNotification($user, $data);
        }
        $nb = $this->notificationService->getNbByUser($user);
        $this->assertEquals(10, $nb);
    }

    /**
     * Test méthode testGetByUserPaginate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetByUserPaginate(): void
    {
        $user = $this->createUser();
        $this->notificationService->add($user, NotificationKey::NOTIFICATION_SELF_DELETE, [
            'login' => $user->getLogin(),
        ]);
        $this->notificationService->add($user, NotificationKey::NOTIFICATION_SELF_DISABLED, [
            'login' => $user->getLogin(),
        ]);
        $this->notificationService->add($user, NotificationKey::NOTIFICATION_SELF_ANONYMOUS, [
            'login' => $user->getLogin(),
        ]);
        $this->notificationService->add($user, NotificationKey::NOTIFICATION_NEW_FONDATEUR, [
            'login' => $user->getLogin(),
            'url_aide' => '#',
        ]);

        $paginator = $this->notificationService->getByUserPaginate(1, 1, $user, true);
        $this->assertEquals(1, $paginator->getIterator()->count());
        $this->assertEquals(4, $paginator->count());

        $notification = $paginator->getIterator()->current();

        $notificationRef = $this->notificationService->findOneById(Notification::class, $notification->getId());
        $parameter = json_decode($notification->getParameters(), true);
        $this->assertEquals(
            $this->translator->trans($notificationRef->getTitle(), domain: 'notification'),
            $notification->getTitle(),
        );
        $notification->setContent(
            $this->translator->trans($notificationRef->getContent(), $parameter, domain: 'notification'),
        );

        for ($i = 0; $i < 5; $i++) {
            $data = ['read' => 1];
            $this->createNotification($user, $data);
        }

        $paginator = $this->notificationService->getByUserPaginate(1, 20, $user, true);
        $this->assertEquals(4, $paginator->count());

        $paginator = $this->notificationService->getByUserPaginate(1, 20, $user, false);
        $this->assertEquals(9, $paginator->count());
    }

    /**
     * Test méthode getTranslateListNotifications()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetTranslateListNotifications(): void
    {
        $result = $this->notificationService->getTranslateListNotifications();
        $this->assertIsArray($result);
    }

    /**
     * test méthode purge()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws NotFoundExceptionInterface
     */
    public function testPurge(): void
    {
        $user = $this->createUser();
        for ($i = 0; $i < 5; $i++) {
            $data = ['read' => 1];
            $this->createNotification($user, $data);
        }
        $this->notificationService->purge(0, $user->getId());

        $nb = $this->notificationService->getNbByUser($user);
        $this->assertEquals(0, $nb);

        for ($i = 0; $i < 5; $i++) {
            $data = ['read' => 0];
            $this->createNotification($user, $data);
        }
        for ($i = 0; $i < 2; $i++) {
            $data = ['read' => 1];
            $this->createNotification($user, $data);
        }

        $this->notificationService->purge(0, $user->getId());
        $nb = $this->notificationService->getNbByUser($user);
        $this->assertEquals(5, $nb);
    }

    /**
     * Test méthode testReadAll()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReadAll(): void
    {
        $user = $this->createUser();
        for ($i = 0; $i < 5; $i++) {
            $data = ['read' => 0];
            $this->createNotification($user, $data);
        }
        $this->notificationService->readAll($user);
        $result = $this->notificationService->findBy(Notification::class, ['read' => 1]);
        $this->assertCount(5, $result);
    }
}
