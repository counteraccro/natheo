<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test controller user
 */
namespace App\Tests\Controller\Admin\System;

use App\Enum\Admin\System\Options\OptionUser as OptionUserEnum;
use App\Entity\Admin\Notification;
use App\Entity\Admin\System\Mail;
use App\Entity\Admin\System\OptionUser;
use App\Entity\Admin\System\User;
use App\Entity\Admin\System\UserData;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Repository\Admin\NotificationRepository;
use App\Repository\Admin\System\OptionUserRepository;
use App\Repository\Admin\System\UserRepository;
use App\Service\Admin\System\MailService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\User\UserDataService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Mail\MailKey;
use App\Utils\System\User\Anonymous;
use App\Enum\Admin\System\User\UserDataKey;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AppWebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test de l'index de la gestion des users
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_user_index');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('user.page_title_h1', domain: 'user'));
    }

    /**
     * Page de modification de ses options
     * @return void
     */
    public function testUserOption(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_my_option'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('user.my_option_title_h1', domain: 'user'));
    }

    /**
     * Test mise à jour d'une option
     * @return void
     */
    public function testUpdateMyOption(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');
        $server = ['HTTP_X-CSRF-TOKEN' => $this->getMyOptionCsrfToken()];

        /** @var OptionUserRepository $repo */
        $repo = $this->em->getRepository(OptionUser::class);
        $getValue = function (string $key) use ($repo, $user): ?string {
            $this->em->clear();
            $option = $repo->findOneBy(['key' => $key, 'user' => $user->getId()]);
            return $option?->getValue();
        };

        $content = $this->postMyOption(['key' => OptionUserEnum::OU_NB_ELEMENT->value, 'value' => 50], $server);
        $this->assertResponseIsSuccessful();
        $this->assertTrue($content['success']);
        $this->assertEquals('50', $getValue(OptionUserEnum::OU_NB_ELEMENT->value));

        $content = $this->postMyOption(
            ['key' => OptionUserEnum::OU_NB_ELEMENT->value, 'value' => 10],
            [
                'HTTP_X-CSRF-TOKEN' => 'jeton-invalide',
            ],
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertFalse($content['success']);
        $this->assertEquals('50', $getValue(OptionUserEnum::OU_NB_ELEMENT->value));

        $content = $this->postMyOption(['key' => OptionUserEnum::OU_NB_ELEMENT->value], $server);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertFalse($content['success']);

        $invalidData = [
            ['key' => OptionUserEnum::OU_NB_ELEMENT->value, 'value' => '7'],
            ['key' => OptionUserEnum::OU_DEFAULT_LANGUAGE->value, 'value' => 'xx'],
            ['key' => OptionUserEnum::OU_THEME_SITE->value, 'value' => 'green'],
            ['key' => 'UNKNOWN_KEY', 'value' => '50'],
        ];
        foreach ($invalidData as $data) {
            $before = $getValue($data['key']);
            $content = $this->postMyOption($data, $server);
            $this->assertResponseIsSuccessful();
            $this->assertFalse($content['success'], $data['key']);
            $this->assertEquals($before, $getValue($data['key']), $data['key']);
        }

        // Option absente en base pour ce user : elle doit être créée
        $option = $repo->findOneBy([
            'key' => OptionUserEnum::OU_DEFAULT_PERSONAL_DATA_RENDER->value,
            'user' => $user->getId(),
        ]);
        $this->em->remove($option);
        $this->em->flush();

        $content = $this->postMyOption(
            ['key' => OptionUserEnum::OU_DEFAULT_PERSONAL_DATA_RENDER->value, 'value' => 'login'],
            $server,
        );
        $this->assertTrue($content['success']);
        $this->assertEquals('login', $getValue(OptionUserEnum::OU_DEFAULT_PERSONAL_DATA_RENDER->value));
    }

    /**
     * Envoie une requête de mise à jour d'une option user et retourne la réponse JSON décodée
     * @param array $data
     * @param array $server
     * @return array
     */
    private function postMyOption(array $data, array $server): array
    {
        $this->client->request(
            'POST',
            $this->router->generate('admin_user_ajax_update_my_option'),
            server: $server,
            content: json_encode($data),
        );
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        return json_decode($response->getContent(), true);
    }

    /**
     * Retourne le jeton CSRF passé au composant Vue de la page "mes options"
     * @return string
     */
    private function getMyOptionCsrfToken(): string
    {
        $crawler = $this->client->request('GET', $this->router->generate('admin_user_my_option'));
        $props = $crawler
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/System/Option"]')
            ->attr('data-symfony--ux-vue--vue-props-value');

        return json_decode($props, true)['csrf_token'];
    }

    /**
     * Test chargement des données du grid
     * @return void
     */
    public function testLoadGridData(): void
    {
        $this->checkNoAccess('admin_user_load_grid_data', ['page' => 1, 'limit' => 10]);

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_user_load_grid_data', ['page' => 1, 'limit' => 10]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(2, $content['nb']);
        $this->assertCount(2, $content['data']);
    }

    /**
     * Les données saisies par les users sont échappées dans le grid
     * @return void
     */
    public function testLoadGridDataEscapeHtml(): void
    {
        $xss = '<img src=x onerror=alert(1)>';
        $this->createUserContributeur(['login' => $xss, 'firstname' => $xss, 'avatar' => null]);

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_user_load_grid_data', ['page' => 1, 'limit' => 10]),
        );
        $this->assertResponseIsSuccessful();
        $content = json_encode(json_decode($this->client->getResponse()->getContent(), true));

        $this->assertStringNotContainsString($xss, $content);
        $this->assertStringContainsString(htmlspecialchars($xss), $content);
    }

    /**
     * Désactivation d'un utilisateur
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $userTODisabled = $this->createUserContributeur([
            'disabled' => false,
        ]);

        $this->checkNoAccess('admin_user_update_disabled', ['id' => $userTODisabled->getId()], 'PUT');

        $userSuperAdmin = $this->createUserSuperAdmin();
        $userFounderToDisabled = $this->createUserFounder();

        $this->client->loginUser($userSuperAdmin, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_user_update_disabled', ['id' => $userFounderToDisabled->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('false', $content['success']);

        $this->client->request(
            'PUT',
            $this->router->generate('admin_user_update_disabled', ['id' => $userTODisabled->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('true', $content['success']);

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $userToCheck = $userRepository->findOneBy(['id' => $userTODisabled->getId()]);
        $this->assertTrue($userToCheck->isDisabled());
    }

    /**
     * Un super admin ne peut être désactivé que par le fondateur
     * @return void
     */
    public function testUpdateDisabledSuperAdmin(): void
    {
        $userSuperAdmin = $this->createUserSuperAdmin();
        $otherSuperAdmin = $this->createUserSuperAdmin();
        $founder = $this->createUserFounder();

        $this->client->loginUser($userSuperAdmin, 'admin');
        foreach ([$otherSuperAdmin, $userSuperAdmin] as $target) {
            $this->client->request(
                'PUT',
                $this->router->generate('admin_user_update_disabled', ['id' => $target->getId()]),
            );
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('false', $content['success']);
        }

        $this->client->loginUser($founder, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_user_update_disabled', ['id' => $otherSuperAdmin->getId()]),
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('true', $content['success']);
    }

    /**
     * Un compte désactivé est déconnecté à la requête suivante
     * @return void
     */
    public function testDisabledUserIsLoggedOut(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_my_account'));
        $this->assertResponseIsSuccessful();

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $userToDisable = $userRepository->find($user->getId());
        $userToDisable->setDisabled(true);
        $this->em->flush();

        $this->client->request('GET', $this->router->generate('admin_user_my_account'));
        $this->assertResponseRedirects();
        $this->assertStringContainsString(
            $this->router->generate('auth_user_login'),
            $this->client->getResponse()->headers->get('Location'),
        );
    }

    /**
     * Test suppression d'un utilisateur
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDelete(): void
    {
        $userToDelete = $this->createUserContributeur();
        $userSuperAdminToDelete = $this->createUserSuperAdmin();

        $this->checkNoAccess('admin_user_delete', ['id' => $userToDelete->getId()], 'DELETE');

        // Tentative delete superadmin
        $userSuperAdmin = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdmin, 'admin');
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_user_delete', ['id' => $userSuperAdminToDelete->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('false', $content['success']);

        // Tentative delete mais sans autorisation
        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, '0');

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_user_delete', ['id' => $userToDelete->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(false, $content['success']);

        // Anonymisation du user
        $optionSystemService->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_REPLACE_DELETE_USER->value, '1');
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_user_delete', ['id' => $userToDelete->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(true, $content['success']);

        $userRepository = $this->em->getRepository(User::class);
        $userToCheck = $userRepository->findOneBy(['id' => $userToDelete->getId()]);
        $this->assertEquals(Anonymous::FIRST_NAME, $userToCheck->getFirstName());
        $this->assertEquals(Anonymous::LAST_NAME, $userToCheck->getLastName());
        $this->assertEquals(Anonymous::LOGIN, $userToCheck->getLogin());

        // Supprimer un utilisateur
        $optionSystemService->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_REPLACE_DELETE_USER->value, '0');
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_user_delete', ['id' => $userToDelete->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(true, $content['success']);
        $userToCheck = $userRepository->findOneBy(['id' => $userToDelete->getId()]);
        $this->assertNull($userToCheck);
    }

    /**
     * Test de la page de mise à jour d'un user
     * @return void
     */
    public function testUpdate(): void
    {
        $userToUpdate = $this->createUserContributeur();
        $userSuperAdmin = $this->createUserSuperAdmin();

        $userFounderToUpdate = $this->createUserFounder();

        $this->checkNoAccess('admin_user_update', ['id' => $userToUpdate->getId()], 'GET');

        $this->client->loginUser($userSuperAdmin, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_user_update', ['id' => $userFounderToUpdate->getId()]),
        );
        $this->assertResponseStatusCodeSame(302);

        $otherSuperAdmin = $this->createUserSuperAdmin();
        $this->client->request(
            'GET',
            $this->router->generate('admin_user_update', ['id' => $otherSuperAdmin->getId()]),
        );
        $this->assertResponseStatusCodeSame(302);

        $this->client->request('GET', $this->router->generate('admin_user_update', ['id' => $userToUpdate->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('user.page_update_title_h1_2', domain: 'user'),
        );
    }

    /**
     * Test changement de son mot de passe
     * @return void
     */
    public function testUpdatePassword(): void
    {
        $currentPassword = 'Current-Pass1';
        $user = $this->createUser(['password' => $currentPassword]);
        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_user_change_my_password');

        // Mot de passe actuel incorrect
        $this->client->request('POST', $url, content: json_encode(['current' => 'bad', 'data' => 'New-Pass123']));
        $this->assertResponseStatusCodeSame(400);

        // Nouveau mot de passe trop faible
        $this->client->request('POST', $url, content: json_encode(['current' => $currentPassword, 'data' => 'weak']));
        $this->assertResponseStatusCodeSame(400);

        $this->client->request(
            'POST',
            $url,
            content: json_encode(['current' => $currentPassword, 'data' => 'New-Pass123']),
        );
        $this->assertResponseIsSuccessful();

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $userToCheck = $userRepository->find($user->getId());
        $this->assertNotEquals($user->getPassword(), $userToCheck->getPassword());
    }

    /**
     * Test suppression de son avatar
     * @return void
     */
    public function testDeleteAvatar(): void
    {
        $user = $this->createUser(['avatar' => 'avatar-test.png']);
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', $this->router->generate('admin_user_delete_avatar'));
        $this->assertResponseStatusCodeSame(405);

        // Sans token CSRF l'avatar est conservé
        $this->client->request('POST', $this->router->generate('admin_user_delete_avatar'));
        $this->assertResponseRedirects($this->router->generate('admin_user_my_account'));

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $this->assertEquals('avatar-test.png', $userRepository->find($user->getId())->getAvatar());

        $crawler = $this->client->request('GET', $this->router->generate('admin_user_my_account'));
        $token = $crawler->filter('#form-delete-avatar input[name="_token"]')->attr('value');
        $this->client->request('POST', $this->router->generate('admin_user_delete_avatar'), ['_token' => $token]);
        $this->assertResponseRedirects($this->router->generate('admin_user_my_account'));

        $this->em->clear();
        $this->assertNull($userRepository->find($user->getId())->getAvatar());
    }

    /**
     * Test de la page de mise à jour de son compte
     * @return void
     */
    public function testUpdateMyAccount(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_my_account'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('user.page_my_account.title_h1', domain: 'user'),
        );
    }

    /**
     * Test auto désactivation
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function testSelfDisabled(): void
    {
        $userFounder = $this->createUserFounder();
        $userSuperAdminToDisable = $this->createUserSuperAdmin();
        $userToDisable = $this->createUser();

        $this->generateDefaultMails();

        // Tentative désactivation superadmin
        $this->client->loginUser($userSuperAdminToDisable, 'admin');
        $this->client->request('POST', $this->router->generate('admin_user_self_disabled'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($this->translator->trans('user.error_not_disabled', domain: 'user'), $content['msg']);

        // Désactivation user
        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_MAIL_NOTIFICATION->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_NOTIFICATION->value, '1');
        $this->client->loginUser($userToDisable, 'admin');
        $this->client->request('POST', $this->router->generate('admin_user_self_disabled'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($this->translator->trans('user.self_disabled_success', domain: 'user'), $content['msg']);

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $userToCheck = $userRepository->findOneBy(['id' => $userToDisable->getId()]);
        $this->assertTrue($userToCheck->isDisabled());

        $mailService = $this->container->get(MailService::class);
        /** @var Mail $mail */
        $mail = $mailService->getByKey(MailKey::MAIL_SELF_DISABLED_ACCOUNT);
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $mail->geMailTranslationByLocale('fr')->getTitle());

        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->em->getRepository(Notification::class);
        $result = $notificationRepository->getNbByUser($userFounder);
        $this->assertEquals(1, $result);
    }

    /**
     * Test de l'auto delete
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws NotFoundExceptionInterface
     */
    public function testSelfDelete(): void
    {
        $userFounder = $this->createUserFounder();
        $userSuperAdminToDelete = $this->createUserSuperAdmin();
        $userToDelete = $this->createUser();

        $this->generateDefaultMails();

        // Delete superadmin
        $this->client->loginUser($userSuperAdminToDelete, 'admin');
        $this->client->request('POST', $this->router->generate('admin_user_self_delete'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($this->translator->trans('user.error_not_disabled', domain: 'user'), $content['msg']);

        //delete user - Anonymisation
        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_MAIL_NOTIFICATION->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_NOTIFICATION->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_REPLACE_DELETE_USER->value, '1');
        $this->client->loginUser($userToDelete, 'admin');
        $this->client->request('POST', $this->router->generate('admin_user_self_delete'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(
            $this->translator->trans('user.danger_zone.success_anonymous', domain: 'user'),
            $content['msg'],
        );

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $userToCheck = $userRepository->findOneBy(['id' => $userToDelete->getId()]);
        $this->assertEquals(Anonymous::FIRST_NAME, $userToCheck->getFirstName());
        $this->assertEquals(Anonymous::LAST_NAME, $userToCheck->getLastName());
        $this->assertEquals(Anonymous::LOGIN, $userToCheck->getLogin());

        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->em->getRepository(Notification::class);
        $result = $notificationRepository->getNbByUser($userFounder);
        $this->assertEquals(1, $result);

        $mailService = $this->container->get(MailService::class);
        /** @var Mail $mail */
        $mail = $mailService->getByKey(MailKey::MAIL_SELF_ANONYMOUS_ACCOUNT);
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $mail->geMailTranslationByLocale('fr')->getTitle());

        //Delete user - delete
        $userToDelete = $this->createUser();
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_MAIL_NOTIFICATION->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_NOTIFICATION->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_REPLACE_DELETE_USER->value, '0');
        $this->client->loginUser($userToDelete, 'admin');
        $this->client->request('POST', $this->router->generate('admin_user_self_delete'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(
            $this->translator->trans('user.danger_zone.success_remove', domain: 'user'),
            $content['msg'],
        );

        $result = $notificationRepository->getNbByUser($userFounder);
        $this->assertEquals(2, $result);
        $userToCheck = $userRepository->findOneBy(['id' => $userToDelete->getId()]);
        $this->assertNull($userToCheck);

        $mail = $mailService->getByKey(MailKey::MAIL_SELF_DELETE_ACCOUNT);
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $mail->geMailTranslationByLocale('fr')->getTitle());
    }

    /**
     * Test page ajout
     * @return void
     */
    public function testAdd(): void
    {
        $this->checkNoAccess('admin_user_add');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('user.page_add_title_h1', domain: 'user'));
    }

    /**
     * Test switch user
     * @return void
     */
    public function testSwitch(): void
    {
        $this->checkNoAccess('admin_user_switch', ['user' => self::getFaker()->email()]);
        $userToSwitch = $this->createUser();
        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_user_switch', ['user' => $userToSwitch->getEmail()]),
        );
        $this->assertResponseStatusCodeSame(302);

        // Prise de contrôle directe du fondateur ou d'un autre super admin refusée
        $founder = $this->createUserFounder();
        $otherSuperAdmin = $this->createUserSuperAdmin();
        foreach ([$founder, $otherSuperAdmin] as $target) {
            $this->client->request(
                'GET',
                $this->router->generate('admin_dashboard_index', ['_switch_user' => $target->getEmail()]),
            );
            $this->assertResponseStatusCodeSame(403);
        }

        $this->client->request(
            'GET',
            $this->router->generate('admin_dashboard_index', ['_switch_user' => $userToSwitch->getEmail()]),
        );
        $this->assertResponseRedirects($this->router->generate('admin_dashboard_index'));
    }

    /**
     * Reset du mot de passe
     * @return void
     */
    public function testSendResetPassword(): void
    {
        $userResetPassword = $this->createUser();
        $userSuperAdmin = $this->createUserSuperAdmin();
        $this->generateDefaultMails();

        $this->checkNoAccess('admin_user_reset_password', ['id' => $userResetPassword->getId()]);

        $this->client->loginUser($userSuperAdmin, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_user_reset_password', ['id' => $userResetPassword->getId()]),
        );
        $this->assertResponseStatusCodeSame(302);

        $userRepository = $this->em->getRepository(User::class);
        $userToCheck = $userRepository->findOneBy(['id' => $userResetPassword->getId()]);
        $email = $this->getMailerMessage();

        // Seul le hash de la clé envoyée par mail est stocké
        $this->assertMatchesRegularExpression('#change-password/([A-Za-z0-9]+)#', $email->getHtmlBody());
        preg_match('#change-password/([A-Za-z0-9]+)#', $email->getHtmlBody(), $matches);
        $this->assertEquals(
            UserDataService::hashResetPasswordKey($matches[1]),
            $userToCheck->getUserDataByKey(UserDataKey::RESET_PASSWORD->value)->getValue(),
        );

        $founder = $this->createUserFounder();
        $this->client->request(
            'GET',
            $this->router->generate('admin_user_reset_password', ['id' => $founder->getId()]),
        );
        $this->assertResponseRedirects($this->router->generate('admin_user_index'));
        $this->assertQueuedEmailCount(0);
    }

    /**
     * Test création / Mise à jour user data
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateUserData(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');

        // Clé non modifiable par l'utilisateur
        $this->client->request(
            'POST',
            $this->router->generate('admin_user_update_user_data'),
            content: json_encode(['key' => UserDataKey::RESET_PASSWORD->value, 'value' => 'test']),
        );
        $this->assertResponseStatusCodeSame(400);

        /** @var UserDataService $userDataService */
        $userDataService = $this->container->get(UserDataService::class);
        $this->assertNull($userDataService->findKeyAndUser(UserDataKey::RESET_PASSWORD->value, $user));

        // Création puis mise à jour
        foreach (['1', '0'] as $value) {
            $this->client->request(
                'POST',
                $this->router->generate('admin_user_update_user_data'),
                content: json_encode(['key' => UserDataKey::HELP_FIRST_CONNEXION->value, 'value' => $value]),
            );
            $this->assertResponseIsSuccessful();
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertTrue($content['success']);

            $this->em->clear();
            $userData = $this->em
                ->getRepository(UserData::class)
                ->findOneBy(['key' => UserDataKey::HELP_FIRST_CONNEXION->value, 'user' => $user->getId()]);
            $this->assertEquals($value, $userData->getValue());
        }
    }
}
