<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du menu Service
 */

namespace App\Tests\Service\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Repository\Admin\Content\Menu\MenuRepository;
use App\Service\Admin\Content\Menu\MenuService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Menu\MenuConst;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MenuServiceTest extends AppWebTestCase
{
    /**
     * @var MenuService $menuService
     */
    private MenuService $menuService;

    public function setUp(): void
    {
        parent::setUp();
        $this->menuService = $this->container->get(MenuService::class);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate(): void
    {
        $user = $this->createUserContributeur();
        $user2 = $this->createUserContributeur();

        $menu = $this->createMenu($user);
        $this->createMenu($user);
        $this->createMenu($user2);

        /** @var Paginator $result */
        $result = $this->menuService->getAllPaginate(1, 20);
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(3, $result->count());

        $result = $this->menuService->getAllPaginate(1, 20, userId: $user2->getId());
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(1, $result->count());

        $result = $this->menuService->getAllPaginate(1, 20, $menu->getName(), $user2->getId());
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(0, $result->count());

        $result = $this->menuService->getAllPaginate(1, 20, $menu->getName(), $user->getId());
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(1, $result->count());
    }

    /**
     * test de la méthode getAllFormatToGrid()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGrid(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $menu = $this->createMenu();
        }

        $result = $this->menuService->getAllFormatToGrid(2, 5);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('nb', $result);
        $this->assertEquals(10, $result['nb']);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(5, $result['data']);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('urlSaveSql', $result);
        $this->assertArrayHasKey('listLimit', $result);
        $this->assertArrayHasKey('translate', $result);

        $last = $result['data'][4];
        $this->assertEquals($menu->getName(), $last['Nom']);
    }

    /**
     * Test méthode getListPosition()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListPosition(): void
    {
        $result = $this->menuService->getListPosition();
        $this->assertIsArray($result);
        $this->assertArrayHasKey(MenuConst::POSITION_HEADER, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_RIGHT, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_FOOTER, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_LEFT, $result);
    }

    /**
     * Test méthode getListType()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListType(): void
    {
        $result = $this->menuService->getListType();
        $this->assertIsArray($result);
        $this->assertArrayHasKey(MenuConst::POSITION_HEADER, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_RIGHT, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_FOOTER, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_LEFT, $result);
    }

    /**
     * Test méthode addMenuElement()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddMenuElement(): void
    {
        $menu = $this->createMenu();
        $id = $this->menuService->addMenuElement($menu->getId(), 1, 1);

        /** @var Menu $verif */
        $verif = $this->menuService->findOneById(Menu::class, $menu->getId());
        $this->assertNotNull($verif);
        $this->assertCount(1, $verif->getMenuElements());

        $id = $this->menuService->addMenuElement($menu->getId(), 1, 1, $id);
        /** @var Menu $verif */
        $verif = $this->menuService->findOneById(Menu::class, $menu->getId());
        $this->assertNotNull($verif);
        $this->assertCount(1, $verif->getMenuElements());
        $menuElement = $verif->getMenuElements()->first();
        $this->assertCount(1, $menuElement->getChildren());

        $this->em->clear();
        /** @var Menu $verif */
        $verif = $this->menuService->findOneById(Menu::class, $menu->getId());
        $this->assertNotNull($verif);
        $this->assertCount(2, $verif->getMenuElements());
    }

    /**
     * Test méthode updateParent()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateParent(): void
    {
        $menu = $this->createMenu();
        $menuElementPos1Row1 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 1]);
        $menuElementPos1Row2 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 2]);
        $menuElementPos1Row3 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 3]);
        $menuElementPos2Row1 = $this->createMenuElement($menu, customData: ['columnPosition' => 2, 'rowPosition' => 1]);

        $this->menuService->updateParent($menuElementPos2Row1->getId(), 1, 1);

        /** @var Menu $verif */
        $verif = $this->menuService->findOneById(Menu::class, $menu->getId());
        $this->assertNotNull($verif);
        $this->assertCount(4, $verif->getMenuElements());
        foreach ($verif->getMenuElements() as $menuElement) {
            switch ($menuElement->getId()) {
                case $menuElementPos2Row1->getId() :
                    $this->assertEquals(1, $menuElement->getColumnPosition());
                    $this->assertEquals(2, $menuElement->getRowPosition());
                    break;
                case $menuElementPos1Row1->getId() :
                    $this->assertEquals(1, $menuElement->getColumnPosition());
                    $this->assertEquals(1, $menuElement->getRowPosition());
                    break;
                case $menuElementPos1Row2->getId() :
                    $this->assertEquals(1, $menuElement->getColumnPosition());
                    $this->assertEquals(3, $menuElement->getRowPosition());
                    break;
                case $menuElementPos1Row3->getId() :
                    $this->assertEquals(1, $menuElement->getColumnPosition());
                    $this->assertEquals(4, $menuElement->getRowPosition());
                    break;
                default :
            }
        }
    }

    /**
     * Test méthode regenerateColumnAndRowPosition()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRegenerateColumnAndRowPosition(): void
    {
        $menu = $this->createMenu();
        $menuElementPos1Row1 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 1]);
        $menuElementPos1Row5 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 5]);
        $menuElementPos1Row7 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 7]);
        $menuElementPos3Row1 = $this->createMenuElement($menu, customData: ['columnPosition' => 3, 'rowPosition' => 1]);
        $menuElementPos3Row4 = $this->createMenuElement($menu, customData: ['columnPosition' => 3, 'rowPosition' => 4]);
        $menuElementPos5Row4 = $this->createMenuElement($menu, customData: ['columnPosition' => 5, 'rowPosition' => 4]);

        $subMenuElementPos1Row3 = $this->createMenuElement($menu, $menuElementPos1Row1, customData: ['columnPosition' => 1, 'rowPosition' => 3]);
        $subMenuElementPos1Row5 = $this->createMenuElement($menu, $menuElementPos1Row1, customData: ['columnPosition' => 1, 'rowPosition' => 5]);

        $this->menuService->regenerateColumnAndRowPosition($menu->getMenuElements()->toArray());

        $verif = $this->menuService->findOneById(Menu::class, $menu->getId());
        $this->assertNotNull($verif);
        $this->assertCount(8, $verif->getMenuElements());
        foreach ($verif->getMenuElements() as $menuElement) {
            /** @var MenuElement $menuElement */
            switch ($menuElement->getId()) {
                case $menuElementPos1Row1->getId() :
                    $this->assertEquals(1, $menuElement->getColumnPosition());
                    $this->assertEquals(1, $menuElement->getRowPosition());

                    foreach ($menuElement->getChildren() as $menuElementChild) {
                        switch ($menuElementChild->getId()) {
                            case $subMenuElementPos1Row3->getId() :
                                $this->assertEquals(1, $menuElementChild->getColumnPosition());
                                $this->assertEquals(1, $menuElementChild->getRowPosition());
                                break;
                            case $subMenuElementPos1Row5->getId() :
                                $this->assertEquals(1, $menuElementChild->getColumnPosition());
                                $this->assertEquals(2, $menuElementChild->getRowPosition());
                                break;
                            default:
                        }
                    }

                    break;
                case $menuElementPos1Row5->getId() :
                    $this->assertEquals(1, $menuElement->getColumnPosition());
                    $this->assertEquals(2, $menuElement->getRowPosition());
                    break;
                case $menuElementPos1Row7->getId() :
                    $this->assertEquals(1, $menuElement->getColumnPosition());
                    $this->assertEquals(3, $menuElement->getRowPosition());
                    break;
                case $menuElementPos3Row1->getId() :
                    $this->assertEquals(2, $menuElement->getColumnPosition());
                    $this->assertEquals(1, $menuElement->getRowPosition());
                    break;
                case $menuElementPos3Row4->getId() :
                    $this->assertEquals(2, $menuElement->getColumnPosition());
                    $this->assertEquals(2, $menuElement->getRowPosition());
                    break;
                case $menuElementPos5Row4->getId() :
                    $this->assertEquals(3, $menuElement->getColumnPosition());
                    $this->assertEquals(1, $menuElement->getRowPosition());
                    break;
                default :
            }
        }

    }

    /**
     * Test méthode getMenuElementByMenuAndParent()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetMenuElementByMenuAndParent(): void
    {
        $menu = $this->createMenu();
        $this->createMenuElement($menu);
        $menuElement = $this->createMenuElement($menu);
        $this->createMenuElement($menu, $menuElement);

        $result = $this->menuService->getMenuElementByMenuAndParent($menu->getId());
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $result = $this->menuService->getMenuElementByMenuAndParent($menu->getId(), $menuElement->getId());
        $this->assertIsArray($result);
        $this->assertCount(1, $result);

    }

    /**
     * Test méthode getListeParentByMenuElement()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListeParentByMenuElement(): void
    {
        $menu = $this->createMenu();
        $menuElement1 = $this->createMenuElement($menu);
        $menuElement = $this->createMenuElement($menu);
        $subMenuElement = $this->createMenuElement($menu, $menuElement);

        $result = $this->menuService->getListeParentByMenuElement($menu->getId(), $menuElement->getId());
        $this->assertIsArray($result);
        $this->assertArrayHasKey($menuElement1->getId(), $result);
        $this->assertEquals(0, $result[$menuElement1->getId()]['deep']);

        $result = $this->menuService->getListeParentByMenuElement($menu->getId(), $menuElement1->getId());
        $this->assertIsArray($result);
        $this->assertArrayHasKey($menuElement->getId(), $result);
        $this->assertEquals(0, $result[$menuElement->getId()]['deep']);
        $this->assertArrayHasKey($subMenuElement->getId(), $result);
        $this->assertEquals(1, $result[$subMenuElement->getId()]['deep']);
    }

    /**
     * Test méthode reorderMenuElement()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReorderMenuElement(): void
    {
        $menu = $this->createMenu();

        $menuElementPos1Row1 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 1]);
        $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 2]);
        $menuElementPos1Row3 = $this->createMenuElement($menu, customData: ['columnPosition' => 1, 'rowPosition' => 3]);
        $this->createMenuElement($menu, $menuElementPos1Row3, customData: ['columnPosition' => 1, 'rowPosition' => 1]);
        $this->createMenuElement($menu, $menuElementPos1Row3, customData: ['columnPosition' => 1, 'rowPosition' => 2]);
        $subMenuElement3 = $this->createMenuElement($menu, $menuElementPos1Row3, customData: ['columnPosition' => 1, 'rowPosition' => 3]);


        $data = [
            "reorderType" => "row",
            "newColumn" => 1,
            "oldColumn" => 1,
            "newRow" => 1,
            "oldRow" => 3,
            "id" => $menuElementPos1Row3->getId(),
            "parent" => "",
            "menu" => $menu->getId()
        ];

        $this->menuService->reorderMenuElement($data);
        /** @var MenuElement $verif */
        $verif = $this->menuService->findOneById(MenuElement::class, $menuElementPos1Row3->getId());
        $this->assertEquals(1, $verif->getRowPosition());

        $verif2 = $this->menuService->findOneById(MenuElement::class, $menuElementPos1Row1->getId());
        $this->assertEquals(2, $verif2->getRowPosition());

        $data = [
            "reorderType" => "column",
            "newColumn" => 2,
            "oldColumn" => 1,
            "newRow" => 1,
            "oldRow" => 3,
            "id" => $subMenuElement3->getId(),
            "parent" => $menuElementPos1Row3->getId(),
            "menu" => $menu->getId()
        ];

        $this->menuService->reorderMenuElement($data);
        /** @var MenuElement $verif */
        $verif = $this->menuService->findOneById(MenuElement::class, $subMenuElement3->getId());
        $this->assertEquals(1, $verif->getRowPosition());
        $this->assertEquals(2, $verif->getColumnPosition());
    }

    /**
     * Test méthode getListMenus()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListMenus() :void {
        for ($i = 0; $i < 10; $i++) {
            $this->createMenu();
        }
        $menu = $this->createMenu(customData : ['disabled' => false]);

        $result = $this->menuService->getListMenus();
        $this->assertIsArray($result);
        $this->assertCount(11, $result);

        $first = $result[array_key_first($result)];
        $this->assertIsArray($first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('disabled', $first);
        $this->assertArrayHasKey('id', $first);

        $check = $result[$menu->getId()];
        $this->assertEquals($check['name'], $menu->getName());
        $this->assertEquals($check['disabled'], $menu->isDisabled());
        $this->assertEquals($check['id'], $menu->getId());
    }

    /**
     * Test méthode switchDefaultMenuToFalse()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSwitchDefaultMenuToFalse() :void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->createMenu(customData : ['position' => MenuConst::POSITION_HEADER, 'defaultMenu' => 0]);
        }
        $menuH = $this->createMenu(customData: ['position' => MenuConst::POSITION_HEADER, 'defaultMenu' => 1]);

        for ($i = 0; $i < 3; $i++) {
            $rMenuF = $this->createMenu(customData : ['position' => MenuConst::POSITION_FOOTER, 'defaultMenu' => 0]);
        }
        $menuF = $this->createMenu(customData: ['position' => MenuConst::POSITION_FOOTER, 'defaultMenu' => 1]);

        $this->menuService->switchDefaultMenuToFalse($rMenuF->getId(), MenuConst::POSITION_FOOTER);

        /** @var MenuRepository $menuRepo */
        $menuRepo = $this->em->getRepository(Menu::class);
        $result = $menuRepo->findAll();
        foreach($result as $menu) {
            /** @var Menu $menu */
            if($menu->getPosition() === MenuConst::POSITION_FOOTER) {
                $this->assertFalse($menu->isDefaultMenu());
            }
            else {
                if($menu->getId() === $menuH->getId()) {
                    $this->assertTrue($menu->isDefaultMenu());
                }
                else {
                    $this->assertFalse($menu->isDefaultMenu());
                }
            }
        }
    }
}