<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller menu
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Repository\Admin\Content\Menu\MenuElementRepository;
use App\Repository\Admin\Content\Menu\MenuRepository;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Menu\MenuConst;

class MenuControllerTest extends AppWebTestCase
{
    /**
     * test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
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
        $this->client->request(
            'GET',
            $this->router->generate('admin_menu_load_grid_data', ['page' => 1, 'limit' => 4]),
        );
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
    public function testDelete(): void
    {
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
    public function testSwitchDefaultMenu(): void
    {
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
    public function testAdd(): void
    {
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

    /**
     * Test méthode load_menu()
     * @return void
     */
    public function testGetMenuById(): void
    {
        $menu = $this->createMenuAllDataDefault();
        $this->checkNoAccess('admin_menu_load_menu', ['id' => $menu->getId()]);

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_load_menu', ['id' => $menu->getId()]));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('menu', $content);
        $this->assertArrayHasKey('data', $content);
    }

    /**
     * Test méthode save()
     * @return void
     */
    public function testSave(): void
    {
        $menu = $this->createMenu();

        $data['menu'] = [
            'id' => $menu->getId(),
            'name' => 'Unit-test',
            'type' => MenuConst::TYPE_FOOTER_1_ROW_CENTER,
            'position' => MenuConst::POSITION_FOOTER,
            'renderOrder' => 1,
            'defaultMenu' => false,
            'disabled' => true,
            'pageMenu' => ['-1'],
            'menuElements' => [
                [
                    'id' => '1',
                    'columnPosition' => 1,
                    'rowPosition' => 1,
                    'linkTarget' => '_self',
                    'disabled' => false,
                    'parent' => '',
                    'page' => '',
                    'menuElementTranslations' => [
                        [
                            'id' => 177,
                            'locale' => 'es',
                            'textLink' => 'es- new',
                            'externalLink' => 'http://www.es.com',
                            'link' => '',
                        ],
                        [
                            'id' => 176,
                            'locale' => 'en',
                            'textLink' => 'en- new',
                            'externalLink' => 'http://www.en.com',
                            'link' => '',
                        ],
                        [
                            'id' => 175,
                            'locale' => 'fr',
                            'textLink' => 'fr- new',
                            'externalLink' => 'http://www.fr.com',
                            'link' => '',
                        ],
                    ],
                ],
            ],
        ];

        $this->checkNoAccess('admin_menu_save_menu', methode: 'POST');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('POST', $this->router->generate('admin_menu_save_menu'), content: json_encode($data));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('redirect', $content);
        $this->assertFalse($content['redirect']);
        $this->assertArrayHasKey('url', $content);
        $this->assertStringContainsString($menu->getId(), $content['url']);

        $menuRepo = $this->em->getRepository(Menu::class);
        $verif = $menuRepo->findOneBy(['id' => $menu->getId()]);
        $this->assertEquals('Unit-test', $verif->getName());

        $data['menu'] = [
            'id' => 0,
            'name' => 'Unit-test',
            'type' => MenuConst::TYPE_FOOTER_1_ROW_CENTER,
            'position' => MenuConst::POSITION_FOOTER,
            'renderOrder' => 1,
            'defaultMenu' => false,
            'disabled' => true,
            'pageMenu' => ['-1'],
            'menuElements' => [
                [
                    'id' => '1',
                    'columnPosition' => 1,
                    'rowPosition' => 1,
                    'linkTarget' => '_self',
                    'disabled' => false,
                    'parent' => '',
                    'page' => '',
                    'menuElementTranslations' => [
                        [
                            'id' => 177,
                            'locale' => 'es',
                            'textLink' => 'es- new',
                            'externalLink' => 'http://www.es.com',
                            'link' => '',
                        ],
                        [
                            'id' => 176,
                            'locale' => 'en',
                            'textLink' => 'en- new',
                            'externalLink' => 'http://www.en.com',
                            'link' => '',
                        ],
                        [
                            'id' => 175,
                            'locale' => 'fr',
                            'textLink' => 'fr- new',
                            'externalLink' => 'http://www.fr.com',
                            'link' => '',
                        ],
                    ],
                ],
            ],
        ];

        $this->checkNoAccess('admin_menu_save_menu', methode: 'POST');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('POST', $this->router->generate('admin_menu_save_menu'), content: json_encode($data));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('redirect', $content);
        $this->assertTrue($content['redirect']);
    }

    /**
     * Test méthode deleteMenuElement()
     * @return void
     */
    public function testDeleteMenuElement(): void
    {
        $menu = $this->createMenu();
        $this->createMenuElement($menu);
        $menuElement = $this->createMenuElement($menu);

        $this->checkNoAccess('admin_menu_delete_menu_element', methode: 'DELETE');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_menu_delete_menu_element', ['id' => $menuElement->getId()]),
        );

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $menuRepo = $this->em->getRepository(Menu::class);
        $verif = $menuRepo->findOneBy(['id' => $menu->getId()]);
        $this->assertCount(1, $verif->getMenuElements()->toArray());
    }

    /**
     * Test méthode newMenuElement()
     * @return void
     */
    public function testNewMenuElement(): void
    {
        $menu = $this->createMenu();

        $data = [
            'idMenu' => $menu->getId(),
            'columP' => 1,
            'rowP' => 1,
            'idParent' => null,
        ];

        $this->checkNoAccess('admin_menu_new_menu_element', methode: 'POST');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'POST',
            $this->router->generate('admin_menu_new_menu_element'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $menuRepo = $this->em->getRepository(Menu::class);
        $verif = $menuRepo->findOneBy(['id' => $menu->getId()]);
        $this->assertCount(1, $verif->getMenuElements()->toArray());
        $menuElement = $verif->getMenuElements()->first();
        $this->assertEquals($content['id'], $menuElement->getId());
    }

    /**
     * Test méthode changeParent()
     * @return void
     */
    public function testChangeParent(): void
    {
        $menu = $this->createMenu();
        $menuElement1 = $this->createMenuElement($menu);
        $menuElement2 = $this->createMenuElement($menu);

        $data = [
            'id' => $menuElement2->getId(),
            'columP' => 1,
            'rowP' => 1,
            'idParent' => $menuElement1->getId(),
        ];

        $this->checkNoAccess('admin_menu_update_parent_menu_element', methode: 'PATCH');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'PATCH',
            $this->router->generate('admin_menu_update_parent_menu_element'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $menuRepo = $this->em->getRepository(MenuElement::class);
        $verif = $menuRepo->findOneBy(['id' => $menuElement1->getId()]);
        $this->assertCount(1, $verif->getChildren()->toArray());

        $verif2 = $menuRepo->findOneBy(['id' => $content['id']]);
        $this->assertEquals($verif->getId(), $verif2->getParent()->getId());
    }

    /**
     * Test méthode getListParent()
     * @return void
     */
    public function testGetListParent(): void
    {
        $menu = $this->createMenu();
        $menuElement1 = $this->createMenuElement($menu);
        $subMenuElement = $this->createMenuElement($menu, $menuElement1);
        $subMenuElement2 = $this->createMenuElement($menu, $menuElement1);
        $subSubMenuElement = $this->createMenuElement($menu, $subMenuElement);

        $this->checkNoAccess('admin_menu_list_parent_menu_element', [
            'menuId' => $menu->getId(),
            'elementId' => $subSubMenuElement->getId(),
        ]);

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_menu_list_parent_menu_element', [
                'menuId' => $menu->getId(),
                'elementId' => $subSubMenuElement->getId(),
            ]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('listParent', $content);
        $this->assertEquals(0, $content['listParent'][$menuElement1->getId()]['deep']);
        $this->assertEquals(1, $content['listParent'][$subMenuElement->getId()]['deep']);
        $this->assertEquals(1, $content['listParent'][$subMenuElement2->getId()]['deep']);
    }

    /**
     * Test méthode reorderMenuElement()
     * @return void
     */
    public function testReorderMenuElement(): void
    {
        $menu = $this->createMenu();

        $menuElementPos1Row1 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 1]);
        $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 2]);
        $menuElementPos1Row3 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 3]);
        $this->createMenuElement($menu, $menuElementPos1Row3, customData: ['columnPosition' => 1, 'rowPosition' => 1]);
        $this->createMenuElement($menu, $menuElementPos1Row3, customData: ['columnPosition' => 1, 'rowPosition' => 2]);
        $subMenuElement3 = $this->createMenuElement(
            $menu,
            $menuElementPos1Row3,
            customData: ['columnPosition' => 1, 'rowPosition' => 3],
        );

        $data['data'] = [
            'reorderType' => 'row',
            'newColumn' => 1,
            'oldColumn' => 1,
            'newRow' => 1,
            'oldRow' => 3,
            'id' => $menuElementPos1Row3->getId(),
            'parent' => '',
            'menu' => $menu->getId(),
        ];

        $this->checkNoAccess('admin_menu_reorder_menu_element', methode: 'PATCH');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'PATCH',
            $this->router->generate('admin_menu_reorder_menu_element'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('id', $content);

        /** @var MenuElementRepository $menuRepo */
        $menuRepo = $this->em->getRepository(MenuElement::class);
        $verif = $menuRepo->findOneById($menuElementPos1Row3->getId());
        $this->assertEquals(1, $verif->getRowPosition());

        $verif2 = $menuRepo->findOneById($menuElementPos1Row1->getId());
        $this->assertEquals(2, $verif2->getRowPosition());
    }
}
