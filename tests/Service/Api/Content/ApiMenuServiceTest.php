<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test API ApiMenuService
 */

namespace Service\Api\Content;

use App\Service\Api\Content\ApiMenuService;
use App\Tests\AppWebTestCase;
use Symfony\Component\DependencyInjection\Container;

class ApiMenuServiceTest extends AppWebTestCase
{
    /**
     *@var ApiMenuService|mixed|object|Container|null
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
     */
    public function testGetMenuForApi() :void
    {
        //$this->apiMenuService->
    }
}