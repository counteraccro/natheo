<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test ApiTokenService
 */
namespace App\Tests\Service\Admin\System;

use App\Entity\Admin\System\ApiToken;
use App\Service\Admin\System\ApiTokenService;
use App\Tests\AppWebTestCase;
use App\Utils\System\ApiToken\ApiTokenConst;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class ApiTokenServiceTest extends AppWebTestCase
{
    /**
     * @var ApiTokenService|mixed|object|Container|null
     */
    private ApiTokenService $apiTokenService;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiTokenService = $this->container->get(ApiTokenService::class);
    }

    /**
     * Test méthode getAllPaginate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->createApiToken();
        }

        $result = $this->apiTokenService->getAllPaginate(1, 5, []);
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(5, $result->getIterator()->count());
        $this->assertEquals(10, $result->count());
    }

    /**
     * Test méthode getAllFormatToGrid()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFormatToGrid(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $token = $this->createApiToken();
        }
        $result = $this->apiTokenService->getAllFormatToGrid(1, 5, []);
        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('translate', $result);
        $this->assertEquals(10, $result['nb']);
        $this->assertCount(5, $result['data']);

        $result = $this->apiTokenService->getAllFormatToGrid(1, 5, ['search' => $token->getName()]);
        $this->assertCount(1, $result['data']);
    }

    /**
     * test méthode generateToken()
     * @return void
     */
    public function testGenerateToken(): void
    {
        $string = $this->apiTokenService->generateToken();
        $this->assertNotEmpty($string);
        $this->assertIsString($string);
    }

    /**
     * test méthode getRolesApi()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetRolesApi(): void
    {
        $result = $this->apiTokenService->getRolesApi();
        $this->assertIsArray($result);
        $this->assertArrayHasKey(ApiTokenConst::API_TOKEN_ROLE_READ, $result);
        $this->assertArrayHasKey(ApiTokenConst::API_TOKEN_ROLE_WRITE, $result);
        $this->assertArrayHasKey(ApiTokenConst::API_TOKEN_ROLE_ADMIN, $result);
    }

    /**
     * Test createUpdateApiToken()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateUpdateApiToken(): void
    {
        $data = [
            'id' => null,
            'name' => 'test',
            'roles' => [ApiTokenConst::API_TOKEN_ROLE_READ],
            'comment' => 'test comment',
            'token' => 'un-token',
        ];
        $id = $this->apiTokenService->createUpdateApiToken($data);
        $this->assertIsInt($id);

        $apiToken = $this->apiTokenService->findOneById(ApiToken::class, $id);
        $this->assertEquals($data['name'], $apiToken->getName());
        $this->assertEquals($data['comment'], $apiToken->getComment());
        $this->assertEquals($data['token'], $apiToken->getToken());

        $data = [
            'id' => $apiToken->getId(),
            'name' => 'test',
            'roles' => [ApiTokenConst::API_TOKEN_ROLE_READ],
            'comment' => 'test comment',
            'token' => 'un-token-edit',
        ];

        $id = $this->apiTokenService->createUpdateApiToken($data);
        $this->assertIsInt($id);

        $apiToken = $this->apiTokenService->findOneById(ApiToken::class, $id);
        $this->assertEquals($data['token'], $apiToken->getToken());
    }

    /**
     * Test méthode getTokenForPreview()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetTokenForPreview(): void
    {
        $result = $this->apiTokenService->getTokenForPreview();
        $this->assertNull($result);

        $apitoken = $this->createApiToken(['disabled' => false]);
        $result = $this->apiTokenService->getTokenForPreview();
        $this->assertIsString($result);
        $this->assertEquals($apitoken->getToken(), $result);
    }
}
