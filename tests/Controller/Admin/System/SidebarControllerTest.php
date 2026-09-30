<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test sidebarController
 */
namespace App\Tests\Controller\Admin\System;

use App\Entity\Admin\System\SidebarElement;
use App\Repository\Admin\System\SidebarElementRepository;
use App\Tests\AppWebTestCase;

class SidebarControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex()
    {
        $this->checkNoAccess('admin_sidebar_index');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_sidebar_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('sidebar.page_title_h1', domain: 'sidebar'));
    }

    /**
     * Test du rendu de la sidebar : sous-menu vide masqué, chevron orienté sur le sous-menu ouvert
     * @return void
     */
    public function testRenderSidebar(): void
    {
        $emptyParent = $this->createSidebarElement([
            'disabled' => false,
            'children' => ['disabled' => true],
        ]);
        $openParent = $this->createSidebarElement([
            'disabled' => false,
            'children' => ['disabled' => false, 'route' => 'admin_sidebar_index'],
        ]);

        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $crawler = $this->client->request('GET', $this->router->generate('admin_sidebar_index'));
        $this->assertResponseIsSuccessful();

        $this->assertCount(0, $crawler->filter('#submenu-' . $emptyParent->getId()));
        $this->assertCount(0, $crawler->filter('#sidebar i.bi'));

        $this->assertStringContainsString('open', $crawler->filter('#submenu-' . $openParent->getId())->attr('class'));
        $this->assertStringContainsString(
            'rotate',
            $crawler->filter('#chevron-' . $openParent->getId())->attr('class'),
        );
    }

    /**
     * Test chargement des données du grid
     * @return void
     */
    public function testLoadGridData(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->createSidebarElement();
        }

        $this->checkNoAccess('admin_sidebar_load_grid_data', ['page' => 1, 'limit' => 8]);

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_sidebar_load_grid_data', ['page' => 1, 'limit' => 8]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(10, $content['nb']);
        $this->assertCount(8, $content['data']);
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $sidebarElement = $this->createSidebarElement(['lock' => false]);

        $this->checkNoAccess('admin_sidebar_update_disabled', ['id' => $sidebarElement->getId()], 'PUT');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_sidebar_update_disabled', ['id' => $sidebarElement->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $this->getCsrfToken()],
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var SidebarElementRepository $repo */
        $repo = $this->em->getRepository(SidebarElement::class);
        $verif = $repo->findOneBy(['id' => $sidebarElement->getId()]);
        $this->assertEquals(!$sidebarElement->isDisabled(), $verif->isDisabled());
    }

    /**
     * Test méthode updateDisabled() sans jeton CSRF valide
     * @return void
     */
    public function testUpdateDisabledWithoutCsrf(): void
    {
        $sidebarElement = $this->createSidebarElement(['lock' => false, 'disabled' => false]);

        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_sidebar_update_disabled', ['id' => $sidebarElement->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide'],
        );
        $this->assertResponseStatusCodeSame(403);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);

        $this->em->clear();
        $verif = $this->em->getRepository(SidebarElement::class)->find($sidebarElement->getId());
        $this->assertFalse($verif->isDisabled());
    }

    /**
     * Test méthode updateDisabled() sur un élément verrouillé
     * @return void
     */
    public function testUpdateDisabledLocked(): void
    {
        $sidebarElement = $this->createSidebarElement(['lock' => true, 'disabled' => false]);

        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_sidebar_update_disabled', ['id' => $sidebarElement->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $this->getCsrfToken()],
        );
        $this->assertResponseStatusCodeSame(403);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);

        $this->em->clear();
        $verif = $this->em->getRepository(SidebarElement::class)->find($sidebarElement->getId());
        $this->assertFalse($verif->isDisabled());
    }

    /**
     * Retourne le jeton CSRF transmis par le grid dans les actions d'un élément non verrouillé
     * @return string
     */
    private function getCsrfToken(): string
    {
        $this->createSidebarElement(['lock' => false]);
        $this->client->request(
            'GET',
            $this->router->generate('admin_sidebar_load_grid_data', ['page' => 1, 'limit' => 50]),
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        foreach ($content['data'] as $row) {
            if (!empty($row['action'])) {
                return $row['action'][0]['csrf'];
            }
        }
        $this->fail('Aucun jeton CSRF trouvé dans les actions du grid');
    }
}
