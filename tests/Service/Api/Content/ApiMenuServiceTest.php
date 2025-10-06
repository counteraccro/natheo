<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test API ApiMenuService
 */

namespace Service\Api\Content;

use App\Dto\Api\Content\Menu\ApiFindMenuDto;
use App\Service\Api\Content\ApiMenuService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class ApiMenuServiceTest extends AppWebTestCase
{
    /**
     * @var ApiMenuService|mixed|object|Container|null
     */
    private ApiMenuService $apiMenuService;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiMenuService = $this->container->get(ApiMenuService::class);
    }

    /**
     * test méthode getMenuForApi()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetMenuForApi(): void
    {
        $menu = $this->createMenuAllDataDefault();

        $dto = new ApiFindMenuDto($menu->getId(), '', 1, 'fr', '');

        $nb = 0;
        foreach ($menu->getMenuElements() as $menuElement) {
            if ($menuElement->getParent() === null && !$menuElement->isDisabled()) {
                $nb++;
            }
        }

        $result = $this->apiMenuService->getMenuForApi($dto);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($menu->getId(), $result['id']);
        $this->assertArrayHasKey('position', $result);
        $this->assertEquals('HEADER', $result['position']);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals($menu->getType(), $result['type']);
        $this->assertArrayHasKey('elements', $result);
        $this->assertCount($nb, $result['elements']);
    }
}
