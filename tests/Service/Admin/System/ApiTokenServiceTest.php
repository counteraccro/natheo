<?php

declare(strict_types=1);
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
use App\Utils\System\ApiToken\TokenHasher;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

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

        // Le jeton CSRF des actions du grid est stocké en session
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->container->get(RequestStack::class)->push($request);
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
     * Test createApiToken()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateApiToken(): void
    {
        $data = [
            'name' => 'test',
            'roles' => ['ROLE_SUPER_ADMIN', ApiTokenConst::API_TOKEN_ROLE_WRITE],
            'comment' => 'test comment',
            'token' => 'un-token',
            'expiresAt' => '2099-01-15',
        ];
        $result = $this->apiTokenService->createApiToken($data);
        $apiToken = $result['apiToken'];
        $this->assertInstanceOf(ApiToken::class, $apiToken);
        $this->assertIsInt($apiToken->getId());

        $apiToken = $this->apiTokenService->findOneById(ApiToken::class, $apiToken->getId());
        $this->assertEquals($data['name'], $apiToken->getName());
        $this->assertEquals($data['comment'], $apiToken->getComment());
        $this->assertFalse($apiToken->isDisabled());
        $this->assertEquals(TokenHasher::hash($result['token']), $apiToken->getToken());
        $this->assertNotEquals(TokenHasher::hash($data['token']), $apiToken->getToken());
        $this->assertEquals([ApiTokenConst::API_TOKEN_ROLE_WRITE, ApiTokenConst::API_TOKEN_ROLE_READ], $apiToken->getRoles());
        $this->assertEquals('2099-01-15 23:59:59', $apiToken->getExpiresAt()->format('Y-m-d H:i:s'));

        $result = $this->apiTokenService->createApiToken(['name' => 'sans role', 'roles' => ['ROLE_INCONNU']]);
        $this->assertEquals([ApiTokenConst::API_TOKEN_ROLE_READ], $result['apiToken']->getRoles());
        $this->assertNull($result['apiToken']->getExpiresAt());
    }

    /**
     * Test updateApiToken()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateApiToken(): void
    {
        $apiToken = $this->createApiToken(['disabled' => true]);
        $hash = $apiToken->getToken();

        $this->apiTokenService->updateApiToken($apiToken, [
            'name' => 'edit',
            'roles' => [ApiTokenConst::API_TOKEN_ROLE_ADMIN],
            'comment' => 'edit comment',
            'token' => 'un-token-edit',
            'expiresAt' => 'date-invalide',
        ]);

        $apiToken = $this->apiTokenService->findOneById(ApiToken::class, $apiToken->getId());
        $this->assertEquals('edit', $apiToken->getName());
        $this->assertEquals($hash, $apiToken->getToken());
        $this->assertTrue($apiToken->isDisabled());
        $this->assertNull($apiToken->getExpiresAt());
        $this->assertContains(ApiTokenConst::API_TOKEN_ROLE_ADMIN, $apiToken->getRoles());
    }

    /**
     * Test regenerateToken()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRegenerateToken(): void
    {
        $apiToken = $this->createApiToken(['lastUsedAt' => new \DateTime()]);
        $hash = $apiToken->getToken();

        $token = $this->apiTokenService->regenerateToken($apiToken);
        $this->assertNotEmpty($token);
        $this->assertNotEquals($hash, $apiToken->getToken());
        $this->assertEquals(TokenHasher::hash($token), $apiToken->getToken());
        $this->assertNull($apiToken->getLastUsedAt());
    }

    /**
     * Test getApiTokenFormData()
     * @return void
     */
    public function testGetApiTokenFormData(): void
    {
        $apiToken = $this->createApiToken([
            'roles' => [ApiTokenConst::API_TOKEN_ROLE_WRITE],
            'expiresAt' => new \DateTime('2099-06-01 23:59:59'),
        ]);
        $result = $this->apiTokenService->getApiTokenFormData($apiToken);
        $this->assertArrayNotHasKey('token', $result);
        $this->assertEquals($apiToken->getId(), $result['id']);
        $this->assertEquals([ApiTokenConst::API_TOKEN_ROLE_WRITE], $result['roles']);
        $this->assertEquals('2099-06-01', $result['expiresAt']);
        $this->assertNull($result['lastUsedAt']);
    }
}
