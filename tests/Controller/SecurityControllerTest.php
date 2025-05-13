<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Tests\Controller;

use App\Entity\Admin\System\User;
use App\Repository\Admin\System\UserRepository;
use App\Tests\AppWebTestCase;
use App\Utils\System\User\UserDataKey;

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
        $this->assertSelectorTextContains('h1.card-title', $this->translator->trans('user.login_user.title', domain: 'user'));

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('auth_user_login'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.card-title', $this->translator->trans('user.login_user.title', domain: 'user'));
    }

    /**
     * Test méthode changePasswordAdm()
     * @return void
     */
    public function testChangePasswordAdm() :void
    {
        $this->client->request('GET', $this->router->generate('auth_change_password_user', ['key' => self::getFaker()->text()]));
        $this->assertResponseStatusCodeSame('404');

        $this->client->request('GET', $this->router->generate('auth_change_new_password_user', ['key' => self::getFaker()->text()]));
        $this->assertResponseStatusCodeSame('404');

        $user = $this->createUser();
        $optionUser = $this->createUserData($user, ['key' => UserDataKey::KEY_RESET_PASSWORD, 'value' => self::getFaker()->text()]);

        $this->client->request('GET', $this->router->generate('auth_change_password_user', ['key' => $optionUser->getValue()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.card-title', $this->translator->trans('user.change_password.title', domain: 'user'));

        $this->client->request('GET', $this->router->generate('auth_change_new_password_user', ['key' => $optionUser->getValue()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.card-title', $this->translator->trans('user.change_password.new_title', domain: 'user'));
    }

    /**
     * Test méthode updatePassword()
     * @return void
     */
    public function testUpdatePassword() :void
    {
        $user = $this->createUser();

        $data = [
            'data' => self::getFaker()->password()
        ];
        $this->client->request('POST', $this->router->generate('auth_change_password_update_user', ['id' => $user->getId()]), content: json_encode($data));
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('redirect', $content);

        /** @var UserRepository $repo */
        $repo = $this->em->getRepository(User::class);
        $verif = $repo->findOneBy(['id' => $user->getId()]);
        $this->assertNotEquals($user->getPassword(), $verif->getPassword());
    }

    /**
     * Test méthode resetPassword()
     * @return void
     */
    public function testResetPassword() :void
    {
        $user = $this->createUser();
        $this->generateDefaultMails();

        $this->client->request('GET', $this->router->generate('auth_reset_password_user'), ['email' => $user->getEmail()]);
        $this->assertResponseIsSuccessful();
        $this->assertQueuedEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $user->getLogin());
    }
}