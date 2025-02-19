<?php

namespace App\Tests\Controller\Admin\System;

use App\Tests\AppWebTestCase;
use Symfony\Component\Routing\Router;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserControllerTest extends AppWebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testIndex() : void
    {
        $this->generateDefaultOptionSystem();
        $user = $this->createUser();
        $this->generateDefaultOptionUser($user);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_index'));

        $this->assertResponseStatusCodeSame(403);

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->generateDefaultOptionUser($userSuperAdm);

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_index'));
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', $this->translator->trans('user.page_title_h1', domain: 'user'));

    }
}