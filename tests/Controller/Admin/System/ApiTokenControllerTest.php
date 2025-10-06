<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Teste ApiTokenController
 */

namespace App\Tests\Controller\Admin\System;

use App\Entity\Admin\System\ApiToken;
use App\Repository\Admin\System\ApiTokenRepository;
use App\Tests\AppWebTestCase;
use App\Utils\System\ApiToken\ApiTokenConst;

class ApiTokenControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_api_token_index');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_api_token_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('api_token.page_title_h1', domain: 'api_token'),
        );
    }

    /**
     * Test méthode loadGridData()
     * @return void
     */
    public function testLoadGridData(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->createApiToken();
        }

        $this->checkNoAccess('admin_api_token_load_grid_data');
        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_api_token_load_grid_data', ['page' => 1, 'limit' => 5]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(10, $content['nb']);
        $this->assertCount(5, $content['data']);
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $apiToken = $this->createApiToken();

        $this->checkNoAccess('admin_api_token_update_disabled', ['id' => $apiToken->getId()], 'PUT');
        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_api_token_update_disabled', ['id' => $apiToken->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var ApiTokenRepository $repo */
        $repo = $this->em->getRepository(ApiToken::class);
        $verif = $repo->findOneBy(['id' => $apiToken->getId()]);
        $this->assertEquals(!$apiToken->isDisabled(), $verif->isDisabled());
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        $apiToken = $this->createApiToken();

        $this->checkNoAccess('admin_api_token_delete', ['id' => $apiToken->getId()], 'DELETE');
        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_api_token_delete', ['id' => $apiToken->getId()]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var ApiTokenRepository $repo */
        $repo = $this->em->getRepository(ApiToken::class);
        $verif = $repo->findOneBy(['id' => $apiToken->getId()]);
        $this->assertNull($verif);
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd(): void
    {
        $this->checkNoAccess('admin_api_token_add');

        $apiToken = $this->createApiToken();

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_api_token_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('api_token.add.page_title_h1', domain: 'api_token'),
        );

        $this->client->request('GET', $this->router->generate('admin_api_token_update', ['id' => $apiToken->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('api_token.update.page_title_h1', domain: 'api_token'),
        );
    }

    /**
     * Test méthode generateToken()
     * @return void
     */
    public function testGenerateToken(): void
    {
        $this->checkNoAccess('admin_api_token_generate_token');
        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_api_token_generate_token'));
        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertIsString($content['token']);
    }

    /**
     * test méthode saveApiToken()
     * @return void
     */
    public function testSaveApiToken(): void
    {
        $this->checkNoAccess('admin_api_token_save', methode: 'POST');
        $data = [
            'apiToken' => [
                'id' => null,
                'name' => self::getFaker()->text(),
                'token' => self::getFaker()->text(),
                'roles' => [ApiTokenConst::API_TOKEN_ROLE_ADMIN],
                'comment' => self::getFaker()->text(),
                'disabled' => false,
            ],
        ];

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('POST', $this->router->generate('admin_api_token_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var ApiTokenRepository $repo */
        $repo = $this->em->getRepository(ApiToken::class);
        $tab = $repo->findAll();
        $this->assertCount(1, $tab);
        $apiToken = $tab[0];
        $this->assertInstanceOf(ApiToken::class, $apiToken);
        $this->assertEquals($data['apiToken']['name'], $apiToken->getName());

        $data = [
            'apiToken' => [
                'id' => $apiToken->getId(),
                'name' => 'Edité',
                'token' => self::getFaker()->text(),
                'roles' => [ApiTokenConst::API_TOKEN_ROLE_ADMIN],
                'comment' => self::getFaker()->text(),
                'disabled' => false,
            ],
        ];
        $this->client->request('POST', $this->router->generate('admin_api_token_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->em->clear();
        $apiTokenCheck = $repo->findOneBy(['id' => $data['apiToken']['id']]);
        $this->assertInstanceOf(ApiToken::class, $apiToken);
        $this->assertEquals($data['apiToken']['name'], $apiTokenCheck->getName());
    }
}
