<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du menu Service
 */

namespace App\Tests\Service\Admin\Content\Menu;

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
    private $menuService;

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
    public function testGetListType() :void
    {
        $result = $this->menuService->getListType();
        $this->assertIsArray($result);
        $this->assertArrayHasKey(MenuConst::POSITION_HEADER, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_RIGHT, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_FOOTER, $result);
        $this->assertArrayHasKey(MenuConst::POSITION_LEFT, $result);
    }
}