<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du menu Service
 */

namespace App\Tests\Service\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Enum\Admin\Content\Menu\MenuPosition;
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
        $result = $this->menuService->getAllPaginate(1, 20, []);
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(3, $result->count());

        $result = $this->menuService->getAllPaginate(1, 20, [], userId: $user2->getId());
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(1, $result->count());

        $result = $this->menuService->getAllPaginate(1, 20, ['search' => $menu->getName()], $user2->getId());
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(0, $result->count());

        $result = $this->menuService->getAllPaginate(1, 20, ['search' => $menu->getName()], $user->getId());
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
        $menu = null;
        for ($i = 0; $i < 10; $i++) {
            $tmp = $this->createMenu();
            if ($i === 0) {
                $menu = $tmp;
            }
        }

        $result = $this->menuService->getAllFormatToGrid(2, 5, []);
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

        //dd($result);

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
        $this->assertArrayHasKey(MenuPosition::POSITION_HEADER->value, $result);
        $this->assertArrayHasKey(MenuPosition::POSITION_RIGHT->value, $result);
        $this->assertArrayHasKey(MenuPosition::POSITION_FOOTER->value, $result);
        $this->assertArrayHasKey(MenuPosition::POSITION_LEFT->value, $result);
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
        $this->assertArrayHasKey(MenuPosition::POSITION_HEADER->value, $result);
        $this->assertArrayHasKey(MenuPosition::POSITION_RIGHT->value, $result);
        $this->assertArrayHasKey(MenuPosition::POSITION_FOOTER->value, $result);
        $this->assertArrayHasKey(MenuPosition::POSITION_LEFT->value, $result);
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
     * Test méthode getListMenus()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListMenus(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->createMenu();
        }
        $menu = $this->createMenu(customData: ['disabled' => false]);

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
    public function testSwitchDefaultMenuToFalse(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->createMenu(customData: ['position' => MenuPosition::POSITION_HEADER->value, 'defaultMenu' => 0]);
        }
        $menuH = $this->createMenu(
            customData: ['position' => MenuPosition::POSITION_HEADER->value, 'defaultMenu' => 1],
        );

        for ($i = 0; $i < 3; $i++) {
            $rMenuF = $this->createMenu(
                customData: ['position' => MenuPosition::POSITION_FOOTER->value, 'defaultMenu' => 0],
            );
        }
        $menuF = $this->createMenu(
            customData: ['position' => MenuPosition::POSITION_FOOTER->value, 'defaultMenu' => 1],
        );

        $this->menuService->switchDefaultMenuToFalse($rMenuF->getId(), MenuPosition::POSITION_FOOTER->value);

        /** @var MenuRepository $menuRepo */
        $menuRepo = $this->em->getRepository(Menu::class);
        $result = $menuRepo->findAll();
        foreach ($result as $menu) {
            /** @var Menu $menu */
            if ($menu->getPosition() === MenuPosition::POSITION_FOOTER->value) {
                $this->assertFalse($menu->isDefaultMenu());
            } else {
                if ($menu->getId() === $menuH->getId()) {
                    $this->assertTrue($menu->isDefaultMenu());
                } else {
                    $this->assertFalse($menu->isDefaultMenu());
                }
            }
        }
    }

    /**
     * Test méthode getErrorDefaultTypeMenu())
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function testGetErrorDefaultTypeMenu(): void
    {
        $this->createMenu(customData: ['position' => MenuPosition::POSITION_HEADER->value, 'defaultMenu' => 0]);
        $this->createMenu(customData: ['position' => MenuPosition::POSITION_LEFT->value, 'defaultMenu' => 1]);
        $this->createMenu(customData: ['position' => MenuPosition::POSITION_FOOTER->value, 'defaultMenu' => 0]);

        $result = $this->menuService->getErrorDefaultTypeMenu();
        $this->assertCount(2, $result);

        $this->createMenu(customData: ['position' => MenuPosition::POSITION_LEFT->value, 'defaultMenu' => 1]);
        $result = $this->menuService->getErrorDefaultTypeMenu();
        $this->assertCount(3, $result);
    }
}
