<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller menu
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Menu\Menu;
use App\Repository\Admin\Content\Menu\MenuRepository;
use App\Tests\AppWebTestCase;

class MenuControllerTest extends AppWebTestCase
{
    /**
     * test méthode index()
     * @return void
     */
    public function testIndex() :void {

        $this->checkNoAccess('admin_menu_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('menu.index.page_title_h1', domain: 'menu'));
    }

    /**
     * Test méthode loadGridData()
     * @return void
     */
    public function testLoadGridData(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createMenuAllDataDefault();
        }

        $this->checkNoAccess('admin_menu_load_grid_data');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_load_grid_data', ['page' => 1, 'limit' => 4]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(5, $content['nb']);
        $this->assertCount(4, $content['data']);
        $this->assertArrayHasKey('nb', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('column', $content);
        $this->assertArrayHasKey('sql', $content);
        $this->assertArrayHasKey('translate', $content);
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $menu = $this->createMenu(customData: ['disabled' => false]);
        $this->checkNoAccess('admin_menu_update_disabled', ['id' => $menu->getId()], methode: 'PUT');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_menu_update_disabled', ['id' => $menu->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        /** @var MenuRepository $verif */
        $menuRepo = $this->em->getRepository(Menu::class);
        $verif = $menuRepo->findOneBy(['id' => $menu->getId()]);
        $this->assertEquals(!$menu->isDisabled(), $verif->isDisabled());
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete() :void {
        $menu = $this->createMenuAllDataDefault();
        $this->checkNoAccess('admin_menu_delete', ['id' => $menu->getId()], methode: 'DELETE');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('DELETE', $this->router->generate('admin_menu_delete', ['id' => $menu->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $menuRepo = $this->em->getRepository(Menu::class);
        $verif = $menuRepo->findOneBy(['id' => $menu->getId()]);
        $this->assertNull($verif);
    }

    /**
     * Test méthode switchDefaultMenu()
     * @return void
     */
    public function testSwitchDefaultMenu() :void {
        $this->createMenu(customData: ['defaultMenu' => true]);
        for ($i = 0; $i < 5; $i++) {
            $menu = $this->createMenu(customData: ['defaultMenu' => false]);
        }

        $this->checkNoAccess('admin_menu_switch_default', ['id' => $menu->getId()], methode: 'PUT');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_menu_switch_default', ['id' => $menu->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $menuRepo = $this->em->getRepository(Menu::class);
        $verif = $menuRepo->findOneBy(['id' => $menu->getId()]);
        $this->assertTrue($verif->isDefaultMenu());

        $liste = $menuRepo->findBy(['defaultMenu' => false]);
        $this->assertCount(5, $liste);
    }

    /**
     * test méthode add()
     * @return void
     */
    public function testAdd() :void {

        $menu = $this->createMenuAllDataDefault();

        $this->checkNoAccess('admin_menu_add');
        $this->checkNoAccess('admin_menu_update', ['id' => $menu->getId()]);

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('menu.add.menu_title_h1', domain: 'menu'));

        $this->client->request('GET', $this->router->generate('admin_menu_update', ['id' => $menu->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('menu.update.menu_title_h1', domain: 'menu'));
    }
}