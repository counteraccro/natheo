<?php

declare(strict_types=1);
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

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

        // Le jeton CSRF des actions du grid est stocké en session
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->container->get(RequestStack::class)->push($request);
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

        $queryParams = [
            'orderField' => 'id',
            'order' => 'ASC',
        ];

        $result = $this->sidebarElementService->getAllPaginate(1, 5, $queryParams);
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

        $queryParams = [
            'orderField' => 'id',
            'order' => 'ASC',
        ];

        $result = $this->sidebarElementService->getAllFormatToGrid(1, 5, $queryParams);
        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(7, $result['nb']);
        $this->assertCount(5, $result['data']);
    }

    /**
     * Test des actions du grid : icônes SVG, jeton CSRF et absence d'action sur un élément verrouillé
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGridActions(): void
    {
        $unlocked = $this->createSidebarElement(['lock' => false, 'disabled' => false]);
        $this->createSidebarElement(['lock' => true, 'disabled' => true]);

        $result = $this->sidebarElementService->getAllFormatToGrid(1, 10, ['orderField' => 'id', 'order' => 'ASC']);
        $this->assertCount(2, $result['data']);
        [$rowUnlocked, $rowLocked] = $result['data'];

        $this->assertCount(1, $rowUnlocked['action']);
        $this->assertNotEmpty($rowUnlocked['action'][0]['csrf']);
        $this->assertStringContainsString(
            $this->router->generate('admin_sidebar_update_disabled', ['id' => $unlocked->getId()]),
            $rowUnlocked['action'][0]['url'],
        );
        $this->assertEmpty($rowLocked['action']);

        $json = json_encode($result['data']);
        $this->assertStringNotContainsString('bi-', $json);
        $this->assertStringContainsString('<svg', $json);
    }

    /**
     * Test méthode getLabelWithIcon() : le tracé de l'icône est échappé
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetLabelWithIcon(): void
    {
        $element = $this->createSidebarElement(['icon' => 'M1 1Z"/><script>', 'label' => 'label-test'], false);

        $html = $this->sidebarElementService->getLabelWithIcon($element);
        $this->assertStringContainsString('d="M1 1Z&quot;/&gt;&lt;script&gt;"', $html);
        $this->assertStringContainsString('label-test', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}
