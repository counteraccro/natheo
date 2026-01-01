<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test controller notification
 */

namespace App\Tests\Controller\Admin;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\User;
use App\Repository\Admin\NotificationRepository;
use App\Repository\Admin\System\UserRepository;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotificationControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->createUser();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_notification_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('notification.page_title_h1', domain: 'notification'),
        );
    }

    /**
     * Test méthode number())
     * @return void
     */
    public function testNumber(): void
    {
        $user = $this->createUser();

        for ($i = 0; $i < 10; $i++) {
            $data = ['read' => 0];
            $this->createNotification($user, $data);
        }

        for ($i = 0; $i < 5; $i++) {
            $data = ['read' => 1];
            $this->createNotification($user, $data);
        }

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_notification_number'));
        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(10, $content['number']);
    }

    /**
     * Test méthode list()
     * @return void
     */
    public function testList(): void
    {
        $user = $this->createUser();

        for ($i = 0; $i < 10; $i++) {
            $data = ['read' => 0];
            $this->createNotification($user, $data);
        }

        for ($i = 0; $i < 5; $i++) {
            $data = ['read' => 1];
            $this->createNotification($user, $data);
        }

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_notification_list', ['page' => 1, 'limit' => 7, 'pOnlyNotRead' => 1]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('notifications', $content);
        $this->assertCount(7, $content['notifications']);
        $this->assertArrayHasKey('listLimit', $content);
        $this->assertArrayHasKey('locale', $content);

        $this->client->request(
            'GET',
            $this->router->generate('admin_notification_list', ['page' => 1, 'limit' => 20, 'pOnlyNotRead' => 0]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertCount(15, $content['notifications']);
    }

    /**
     * Test méthode read()
     * @return void
     */
    public function testUpdateNotification(): void
    {
        $user = $this->createUser();
        $data = ['read' => 0];
        $notification = $this->createNotification($user, $data);

        $tab = [
            'id' => $notification->getId(),
            'isRead' => $notification->isRead(),
        ];

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'POST',
            $this->router->generate('admin_notification_update'),
            content: json_encode(['notifications' => [$tab], 'read' => true]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var NotificationRepository $NotificationRepository */
        $notificationRepository = $this->em->getRepository(Notification::class);
        $notificationVerif = $notificationRepository->findOneBy(['id' => $notification->getId()]);
        $this->assertTrue($notificationVerif->isRead());
    }

    /**
     * Test méthode deleteNotification
     * @return void
     */
    public function testDeleteNotification()
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

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'POST',
            $this->router->generate('admin_notification_delete'),
            content: json_encode(['notifications' => $tab]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var NotificationRepository $NotificationRepository */
        $notificationRepository = $this->em->getRepository(Notification::class);
        $nb = $notificationRepository->count(['user' => $user->getId()]);
        $this->assertEquals(0, $nb);
    }

    /**
     * Test méthode purge()
     * @return void
     * @throws \DateInvalidOperationException
     */
    public function testPurge(): void
    {
        $user = $this->createUser();
        for ($i = 0; $i < 10; $i++) {
            $data = ['read' => 0];
            $this->createNotification($user, $data);
        }

        for ($i = 0; $i < 5; $i++) {
            $data = ['read' => 1];
            $notification = $this->createNotification($user, $data);
            $date = new \DateTime();
            $notification->setCreatedAt($date->sub(new \DateInterval('P6M')));
            $this->em->persist($notification);
        }
        $this->em->flush();

        $this->client->loginUser($user, 'admin');
        $this->client->request('POST', $this->router->generate('admin_notification_purge'));
        $this->em->clear();
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
    }

    public function testReadAll(): void
    {
        $user = $this->createUser();
        for ($i = 0; $i < 5; $i++) {
            $data = ['read' => 0];
            $this->createNotification($user, $data);
        }

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_notification_read_all'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['id' => $user->getId()]);
        foreach ($user->getNotifications() as $notification) {
            $this->assertTrue($notification->isRead());
        }
    }

    /**
     * Test de la méthode getStatistics()
     * @return void
     */
    public function testgetStatistics()
    {
        $user = $this->createUser();
        for ($i = 0; $i < 10; $i++) {
            $data = ['read' => $i % 2];
            $this->createNotification($user, $data);
        }

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_notification_statistics'));
        $this->em->clear();
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('nb_noRead', $content);
        $this->assertArrayHasKey('nb_today', $content);
        $this->assertArrayHasKey('nb_total', $content);
        $this->assertEquals(5, $content['nb_noRead']);
        $this->assertEquals(10, $content['nb_today']);
        $this->assertEquals(10, $content['nb_total']);
    }
}
