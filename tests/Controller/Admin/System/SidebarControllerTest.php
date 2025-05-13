<?php
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
        $this->client->request('GET', $this->router->generate('admin_sidebar_load_grid_data', ['page' => 1, 'limit' => 8]));
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
        $sidebarElement = $this->createSidebarElement();

        $this->checkNoAccess('admin_sidebar_update_disabled', ['id' => $sidebarElement->getId()], 'PUT');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_sidebar_update_disabled', ['id' => $sidebarElement->getId()]));
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
}