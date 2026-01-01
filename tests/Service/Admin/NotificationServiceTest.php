<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test service notification
 */

namespace App\Tests\Service\Admin;

use App\Entity\Admin\Notification;
use App\Enum\Admin\Global\Notification\KeyConfig;
use App\Enum\Admin\Global\Notification\Notification as NotificationEnum;
use App\Entity\Admin\System\User;
use App\Repository\Admin\NotificationRepository;
use App\Service\Admin\NotificationService;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
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

        $this->notificationService->add($user, NotificationEnum::SELF_DELETE->value, [
            'login' => $user->getLogin(),
        ]);

        $user = $this->notificationService->findOneById(User::class, $user->getId());
        $this->assertCount(0, $user->getNotifications());

        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '1');

        $this->notificationService->add($user, NotificationEnum::SELF_DELETE->value, [
            'login' => $user->getLogin(),
        ]);
        $user = $this->notificationService->findOneById(User::class, $user->getId());
        $this->assertCount(1, $user->getNotifications());
        $notification = $user->getNotifications()->first();

        $tab = NotificationEnum::getNotification(NotificationEnum::SELF_DELETE->value);
        $this->assertEquals($tab[KeyConfig::TITLE->value], $notification->getTitle());
        $this->assertEquals($tab[KeyConfig::CONTENT->value], $notification->getContent());
        $this->assertEquals($tab[KeyConfig::LEVEL->value], $notification->getLevel());
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
        $user = $this->notificationService->addForFixture($user, NotificationEnum::SELF_DISABLED->value, [
            'login' => $user->getLogin(),
        ]);
        $this->assertCount(0, $user->getNotifications());

        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '1');
        $user = $this->notificationService->addForFixture($user, NotificationEnum::SELF_DISABLED->value, [
            'login' => $user->getLogin(),
        ]);
        $this->assertCount(1, $user->getNotifications());

        $notification = $user->getNotifications()->first();

        $tab = NotificationEnum::getNotification(NotificationEnum::SELF_DISABLED->value);
        $this->assertEquals($tab[KeyConfig::TITLE->value], $notification->getTitle());
        $this->assertEquals($tab[KeyConfig::CONTENT->value], $notification->getContent());
        $this->assertEquals($tab[KeyConfig::LEVEL->value], $notification->getLevel());
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
        $this->notificationService->add($user, NotificationEnum::SELF_DELETE->value, [
            'login' => $user->getLogin(),
        ]);
        $this->notificationService->add($user, NotificationEnum::SELF_DISABLED->value, [
            'login' => $user->getLogin(),
        ]);
        $this->notificationService->add($user, NotificationEnum::SELF_ANONYMOUS->value, [
            'login' => $user->getLogin(),
        ]);
        $this->notificationService->add($user, NotificationEnum::NEW_FONDATEUR->value, [
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

    /**
     * Test de la méthode getStatisticByUser()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetStatisticByUser(): void
    {
        $user = $this->createUser();
        for ($i = 0; $i < 10; $i++) {
            $data = ['read' => $i % 2];
            $this->createNotification($user, $data);
        }

        $result = $this->notificationService->getStatisticByUser($user);
        $this->assertArrayHasKey('nb_noRead', $result);
        $this->assertArrayHasKey('nb_today', $result);
        $this->assertArrayHasKey('nb_total', $result);
        $this->assertEquals(5, $result['nb_noRead']);
        $this->assertEquals(10, $result['nb_today']);
        $this->assertEquals(10, $result['nb_total']);
    }

    /**
     * Test méthode removeArrayNotifications()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRemoveArrayNotifications(): void
    {
        $user = $this->createUser();
        $tab = [];
        for ($i = 0; $i < 10; $i++) {
            $data = ['read' => 0];
            $notification = $this->createNotification($user, $data);
            $tab[] = [
                'id' => $notification->getId(),
            ];
        }
        $this->notificationService->removeArrayNotifications($tab);
        /** @var NotificationRepository $NotificationRepository */
        $notificationRepository = $this->em->getRepository(Notification::class);
        $nb = $notificationRepository->count(['user' => $user->getId()]);
        $this->assertEquals(0, $nb);
    }

    /**
     * Test méthode updateRead
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateRead(): void
    {
        $user = $this->createUser();
        $data = ['read' => 0];
        $notification = $this->createNotification($user, $data);

        $tab = [
            'id' => $notification->getId(),
            'isRead' => $notification->isRead(),
        ];
        $this->notificationService->updateRead([$tab], true);
        /** @var NotificationRepository $NotificationRepository */
        $notificationRepository = $this->em->getRepository(Notification::class);
        $notificationVerif = $notificationRepository->findOneBy(['id' => $notification->getId()]);
        $this->assertTrue($notificationVerif->isRead());
    }
}
