<?php

namespace App\Tests\Controller\Admin\System;

use App\Entity\Admin\System\OptionUser;
use App\Entity\Admin\System\User;
use App\Repository\Admin\System\OptionUserRepository;
use App\Repository\Admin\System\UserRepository;
use App\Tests\AppWebTestCase;
use App\Utils\System\Options\OptionUserKey;

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
    public function testIndex() : void
    {
        $this->checkNoAccess('admin_user_index');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->generateDefaultOptionUser($userSuperAdm);

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
        $this->generateDefaultOptionUser($user);
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_my_option'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('user.my_option_title_h1', domain: 'user'));
    }

    /**
     * Test mise à jour d'une option
     * @return void
     */
    public function testUpdateMyOption() : void
    {
        $user = $this->createUser();
        $this->generateDefaultOptionUser($user);
        $this->client->loginUser($user, 'admin');

        $parameters = [
            'key' => OptionUserKey::OU_NB_ELEMENT,
            'value' => 50
        ];
        $this->client->request('POST', $this->router->generate('admin_user_ajax_update_my_option'), content: json_encode($parameters));
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
        $this->generateDefaultOptionUser($userSuperAdm);
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_load_grid_data', ['page' => 1, 'limit' => 10]));
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
    public function testUpdateDisabled() : void
    {
        $userTODisabled = $this->createUserContributeur([
            'disabled' => false
        ]);

        $this->checkNoAccess('admin_user_update_disabled', ['id' => $userTODisabled->getId()], 'PUT');

        $userSuperAdmin = $this->createUserSuperAdmin();
        $userFounderToDisabled = $this->createUserFounder();

        $this->client->loginUser($userSuperAdmin, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_user_update_disabled', ['id' => $userFounderToDisabled->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('false', $content['success']);

        $this->client->request('PUT', $this->router->generate('admin_user_update_disabled', ['id' => $userTODisabled->getId()]));
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
}