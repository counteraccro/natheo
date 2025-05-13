<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test Service InformationService
 */
namespace App\Tests\Service\Admin\System;

use App\Service\Admin\System\InformationService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class InformationServiceTest extends AppWebTestCase
{
    /**
     * @var mixed|object|Container|null
     */
    private InformationService $informationService;

    public function  setUp(): void
    {
        parent::setUp();
        $this->informationService = $this->container->get(InformationService::class);
        $_SERVER['SERVER_SOFTWARE'] = self::getFaker()->text();
        $_SERVER['REQUEST_SCHEME'] = self::getFaker()->domainName();
        $_SERVER['HTTP_HOST'] = self::getFaker()->text();
        $_SERVER['HTTP_USER_AGENT'] = self::getFaker()->userAgent();
    }

    /**
     * Test méthode getAllInformation()
     * @return void
     */
    public function testGetAllInformation(): void
    {
        $result = $this->informationService->getAllInformation();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('serveur', $result);
        $this->assertArrayHasKey('database', $result);
        $this->assertArrayHasKey('website', $result);
        $this->assertArrayHasKey('navigateur', $result);
    }

    /**
     * Test méthode getInformationServer()
     * @return void
     */
    public function testGetInformationServer(): void
    {
        $result = $this->informationService->getInformationServer();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('server_software_version', $result);
        $this->assertArrayHasKey('php_version', $result);
        $this->assertArrayHasKey('memory_limit', $result);
        $this->assertArrayHasKey('upload_max_file_size', $result);
    }

    /**
     * Test méthode getInformationDatabase()
     * @return void
     */
    public function testGetInformationDatabase() :void
    {
        $result = $this->informationService->getInformationDatabase();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('database_type', $result);
        $this->assertArrayHasKey('database_user', $result);
        $this->assertArrayHasKey('database_server', $result);
        $this->assertArrayHasKey('database_name', $result);
    }

    /**
     * Test méthode getInformationWebsite()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetInformationWebsite() :void
    {
        $result = $this->informationService->getInformationWebsite();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('website_version', $result);
        $this->assertArrayHasKey('website_url', $result);
    }

    /**
     * Test méthode getInformationNavigateur()
     * @return void
     */
    public function testGetInformationNavigateur() :void
    {
        $result = $this->informationService->getInformationNavigateur();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('navigateur_info', $result);
    }
}