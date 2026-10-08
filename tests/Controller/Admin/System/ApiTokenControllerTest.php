<?php

declare(strict_types=1);
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
use App\Utils\System\ApiToken\TokenHasher;

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
            server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide'],
        );
        $this->assertResponseStatusCodeSame(403);

        $this->client->request(
            'PUT',
            $this->router->generate('admin_api_token_update_disabled', ['id' => $apiToken->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $this->getGridCsrfToken('put')],
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
            server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide'],
        );
        $this->assertResponseStatusCodeSame(403);

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_api_token_delete', ['id' => $apiToken->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $this->getGridCsrfToken('delete')],
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

        // Le hash du token ne doit jamais être envoyé à la page
        $props = $this->getFormProps();
        $this->assertArrayNotHasKey('token', $props['pApiToken']);
        $this->assertStringNotContainsString($apiToken->getToken(), $this->client->getResponse()->getContent());
    }

    /**
     * Test méthode regenerateToken()
     * @return void
     */
    public function testRegenerateToken(): void
    {
        $apiToken = $this->createApiToken(['disabled' => false]);
        $oldHash = $apiToken->getToken();

        $this->checkNoAccess('admin_api_token_regenerate', ['id' => $apiToken->getId()], 'PUT');
        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $url = $this->router->generate('admin_api_token_regenerate', ['id' => $apiToken->getId()]);
        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide']);
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('GET', $this->router->generate('admin_api_token_update', ['id' => $apiToken->getId()]));
        $csrf = $this->getFormProps()['csrfTokens']['regenerate'];

        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertNotEmpty($content['token']);

        $this->em->clear();
        $verif = $this->em->getRepository(ApiToken::class)->find($apiToken->getId());
        $this->assertNotEquals($oldHash, $verif->getToken());
        $this->assertEquals(TokenHasher::hash($content['token']), $verif->getToken());
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
                'token' => 'token-impose-par-le-client',
                'roles' => [ApiTokenConst::API_TOKEN_ROLE_ADMIN],
                'comment' => self::getFaker()->text(),
                'expiresAt' => '2099-12-31',
                'disabled' => false,
            ],
        ];

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->client->request(
            'POST',
            $this->router->generate('admin_api_token_save'),
            server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide'],
            content: json_encode($data),
        );
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('GET', $this->router->generate('admin_api_token_add'));
        $csrf = $this->getFormProps()['csrfTokens']['save'];

        $this->client->request(
            'POST',
            $this->router->generate('admin_api_token_save'),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertNotEmpty($content['token']);

        /** @var ApiTokenRepository $repo */
        $repo = $this->em->getRepository(ApiToken::class);
        $tab = $repo->findAll();
        $this->assertCount(1, $tab);
        $apiToken = $tab[0];
        $this->assertInstanceOf(ApiToken::class, $apiToken);
        $this->assertEquals($data['apiToken']['name'], $apiToken->getName());
        $this->assertEquals('2099-12-31', $apiToken->getExpiresAt()->format('Y-m-d'));
        // Le token envoyé par le client est ignoré, seul le hash du token généré est stocké
        $this->assertEquals(TokenHasher::hash($content['token']), $apiToken->getToken());
        $hash = $apiToken->getToken();

        $apiToken->setDisabled(true);
        $this->em->flush();

        $data = [
            'apiToken' => [
                'id' => $apiToken->getId(),
                'name' => 'Edité',
                'token' => 'token-impose-par-le-client',
                'roles' => ['ROLE_SUPER_ADMIN'],
                'comment' => self::getFaker()->text(),
                'expiresAt' => '',
                'disabled' => false,
            ],
        ];
        $this->client->request(
            'POST',
            $this->router->generate('admin_api_token_save'),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('', $content['token']);

        $this->em->clear();
        $apiTokenCheck = $repo->findOneBy(['id' => $data['apiToken']['id']]);
        $this->assertEquals($data['apiToken']['name'], $apiTokenCheck->getName());
        $this->assertEquals($hash, $apiTokenCheck->getToken());
        $this->assertTrue($apiTokenCheck->isDisabled());
        $this->assertNull($apiTokenCheck->getExpiresAt());
        $this->assertNotContains('ROLE_SUPER_ADMIN', $apiTokenCheck->getRoles());

        $data['apiToken']['id'] = 999999;
        $this->client->request(
            'POST',
            $this->router->generate('admin_api_token_save'),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode($data),
        );
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Retourne le jeton CSRF d'une action du grid en fonction de son type (put, delete)
     * @param string $type
     * @return string
     */
    private function getGridCsrfToken(string $type): string
    {
        $this->client->request('GET', $this->router->generate('admin_api_token_load_grid_data', ['page' => 1]));
        $content = json_decode($this->client->getResponse()->getContent(), true);
        foreach ($content['data'] as $row) {
            foreach ($row['action'] as $action) {
                if (is_array($action) && ($action['type'] ?? '') === $type && isset($action['csrf'])) {
                    return $action['csrf'];
                }
            }
        }
        $this->fail('Aucun jeton CSRF trouvé dans les actions du grid pour le type ' . $type);
    }

    /**
     * Retourne les props du composant Vue ApiToken de la dernière page chargée
     * @return array
     */
    private function getFormProps(): array
    {
        $props = $this->client
            ->getCrawler()
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/System/ApiToken"]')
            ->attr('data-symfony--ux-vue--vue-props-value');

        return json_decode($props, true);
    }
}
