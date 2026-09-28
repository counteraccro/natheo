<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Tests\Controller;

use App\Entity\Admin\System\User;
use App\Repository\Admin\System\UserRepository;
use App\Service\Admin\System\User\UserDataService;
use App\Tests\AppWebTestCase;
use App\Enum\Admin\System\User\UserDataKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SecurityControllerTest extends AppWebTestCase
{
    /**
     * Test méthode login()
     * @return void
     */
    public function testLogin()
    {
        $this->client->request('GET', $this->router->generate('auth_user_login'));
        $this->assertResponseRedirects($this->router->generate('installation_step_2'));

        $user = $this->createUser();

        $this->client->request('GET', $this->router->generate('auth_user_login'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', $this->translator->trans('user.login_user.title', domain: 'user'));

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('auth_user_login'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', $this->translator->trans('user.login_user.title', domain: 'user'));
    }

    /**
     * Test méthode changePasswordAdm()
     * @return void
     */
    public function testChangePasswordAdm(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('auth_change_password_user', ['key' => self::getFaker()->text()]),
        );
        $this->assertResponseStatusCodeSame(404);

        $this->client->request(
            'GET',
            $this->router->generate('auth_change_new_password_user', ['key' => self::getFaker()->text()]),
        );
        $this->assertResponseStatusCodeSame(404);

        $user = $this->createUser();
        $key = 'resetKeyTest';
        $this->createUserData($user, [
            'key' => UserDataKey::RESET_PASSWORD->value,
            'value' => UserDataService::hashResetPasswordKey($key),
        ]);

        $this->client->request('GET', $this->router->generate('auth_change_password_user', ['key' => $key]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', $this->translator->trans('user.change_password.title', domain: 'user'));

        $this->client->request('GET', $this->router->generate('auth_change_new_password_user', ['key' => $key]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'p',
            $this->translator->trans('user.change_password.new_title', domain: 'user'),
        );
    }

    /**
     * Test méthode updatePassword()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdatePassword(): void
    {
        $user = $this->createUser();
        /** @var UserDataService $userDataService */
        $userDataService = $this->container->get(UserDataService::class);
        $key = $userDataService->generateResetPasswordKey($user);

        // Clé inconnue
        $this->client->request(
            'POST',
            $this->router->generate('auth_change_password_update_user', ['key' => 'badKey']),
            content: json_encode(['data' => 'New-Pass123']),
        );
        $this->assertResponseStatusCodeSame(404);

        // Mot de passe trop faible
        $this->client->request(
            'POST',
            $this->router->generate('auth_change_password_update_user', ['key' => $key]),
            content: json_encode(['data' => 'weak']),
        );
        $this->assertResponseStatusCodeSame(400);

        $this->client->request(
            'POST',
            $this->router->generate('auth_change_password_update_user', ['key' => $key]),
            content: json_encode(['data' => 'New-Pass123']),
        );
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('redirect', $content);

        /** @var UserRepository $repo */
        $repo = $this->em->getRepository(User::class);
        $verif = $repo->findOneBy(['id' => $user->getId()]);
        $this->assertNotEquals($user->getPassword(), $verif->getPassword());

        // La clé n'est plus utilisable
        $this->client->request(
            'POST',
            $this->router->generate('auth_change_password_update_user', ['key' => $key]),
            content: json_encode(['data' => 'Other-Pass123']),
        );
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test méthode resetPassword()
     * @return void
     */
    public function testResetPassword(): void
    {
        $user = $this->createUser();
        $this->generateDefaultMails();

        $this->client->request('GET', $this->router->generate('auth_reset_password_user'), [
            'email' => $user->getEmail(),
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertQueuedEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $user->getLogin());
    }
}
