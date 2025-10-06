<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test controller user
 */
namespace App\Tests\Controller\Admin\System;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\Mail;
use App\Entity\Admin\System\OptionUser;
use App\Entity\Admin\System\User;
use App\Repository\Admin\NotificationRepository;
use App\Repository\Admin\System\OptionUserRepository;
use App\Repository\Admin\System\UserRepository;
use App\Service\Admin\System\MailService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\User\UserDataService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Mail\MailKey;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\System\User\Anonymous;
use App\Utils\System\User\UserDataKey;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

        $parameters = [
            'key' => OptionUserKey::OU_NB_ELEMENT,
            'value' => 50,
        ];
        $this->client->request(
            'POST',
            $this->router->generate('admin_user_ajax_update_my_option'),
            content: json_encode($parameters),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        /** @var OptionUserRepository $repo */
        $repo = $this->em->getRepository(OptionUser::class);
        $optionUser = $repo->findBy(['key' => OptionUserKey::OU_NB_ELEMENT, 'user' => $user->getId()]);
        $this->assertEquals(50, $optionUser[0]->getValue());
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
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_ALLOW_DELETE_DATA, '0');

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
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_ALLOW_DELETE_DATA, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_REPLACE_DELETE_USER, '1');
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
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_ALLOW_DELETE_DATA, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_REPLACE_DELETE_USER, '0');
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

        $this->client->request('GET', $this->router->generate('admin_user_update', ['id' => $userToUpdate->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('user.page_update_title_h1_2', domain: 'user'),
        );
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
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_MAIL_NOTIFICATION, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '1');
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
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_MAIL_NOTIFICATION, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_ALLOW_DELETE_DATA, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_REPLACE_DELETE_USER, '1');
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
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_MAIL_NOTIFICATION, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_ALLOW_DELETE_DATA, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_REPLACE_DELETE_USER, '0');
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
        $this->assertEmailHtmlBodyContains(
            $email,
            $userToCheck->getUserDataByKey(UserDataKey::KEY_RESET_PASSWORD)->getValue(),
        );
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

        $value = self::getFaker()->text(20);

        // Création
        $data = [
            'key' => UserDataKey::KEY_RESET_PASSWORD,
            'value' => $value,
        ];

        $this->client->request(
            'POST',
            $this->router->generate('admin_user_update_user_data'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        $userRepository = $this->em->getRepository(User::class);
        $userToCheck = $userRepository->findOneBy(['id' => $user->getId()]);
        $this->assertEquals($value, $userToCheck->getUserDataByKey(UserDataKey::KEY_RESET_PASSWORD)->getValue());

        // Update
        $value2 = self::getFaker()->text(20);
        $data = [
            'key' => UserDataKey::KEY_RESET_PASSWORD,
            'value' => $value2,
        ];

        $this->client->request(
            'POST',
            $this->router->generate('admin_user_update_user_data'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var UserDataService $userDataService */
        $userDataService = $this->container->get(UserDataService::class);
        $userData = $userDataService->findKeyAndUser(UserDataKey::KEY_RESET_PASSWORD, $user);
        $this->assertEquals($value2, $userData->getValue());
    }
}
