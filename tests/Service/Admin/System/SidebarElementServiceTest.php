<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test SidebarElementService
 */
namespace App\Tests\Service\Admin\System;

use App\Entity\Admin\System\SidebarElement;
use App\Repository\Admin\System\SidebarElementRepository;
use App\Service\Admin\System\SidebarElementService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SidebarElementServiceTest extends AppWebTestCase
{
    /**
     * @var SidebarElementService
     */
    private SidebarElementService $sidebarElementService;

    /**
     * @var SidebarElementRepository
     */
    private SidebarElementRepository $sidebarElementRepository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->sidebarElementService = $this->container->get(SidebarElementService::class);
        $this->sidebarElementRepository = $this->em->getRepository(SidebarElement::class);
    }

    /**
     * Test méthode getAllParent()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllParent(): void
    {
        $this->createSidebarElement(['children' => ['disabled' => false], 'disabled' => false]);
        $this->createSidebarElement(['disabled' => false]);
        $this->createSidebarElement(['disabled' => true]);

        $result = $this->sidebarElementService->getAllParent();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $result = $this->sidebarElementService->getAllParent(true);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Teste la méthode getAllPaginate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate(): void
    {
        $this->createSidebarElement(['children' => ['disabled' => true], 'disabled' => false]);

        for ($i = 0; $i < 5; $i++) {
            $this->createSidebarElement();
        }

        $result = $this->sidebarElementService->getAllPaginate(1, 5);
        $this->assertInstanceOf(\Doctrine\ORM\Tools\Pagination\Paginator::class, $result);
        $this->assertEquals(5, $result->getIterator()->count());
        $this->assertEquals(7, $result->count());
    }

    /**
     * Test méthode getAllFormatToGrid()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGrid(): void
    {
        $this->createSidebarElement(['children' => ['disabled' => true], 'disabled' => false]);

        for ($i = 0; $i < 5; $i++) {
            $this->createSidebarElement();
        }

        $result = $this->sidebarElementService->getAllFormatToGrid(1, 5);
        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(7, $result['nb']);
        $this->assertCount(5, $result['data']);
    }
}
