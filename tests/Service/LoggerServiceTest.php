<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test service Logger
 */

namespace App\Tests\Service;

use App\Service\LoggerService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LogLevel;

class LoggerServiceTest extends AppWebTestCase
{
    /**
     * @var LoggerService
     */
    private LoggerService $loggerService;

    public function setUp(): void
    {
        parent::setUp();
        $this->loggerService = $this->container->get(LoggerService::class);
    }

    /**
     * test méthode logAuthAdmin()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogAuthAdmin(): void
    {
        $user = $this->createUser();

        $logFile = 'auth-' . date('Y-m-d') . '.log';
        $result = $this->loggerService->deleteLog($logFile);
        $this->assertTrue($result);
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', true);
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $data = $result['data'];
        $this->assertStringContainsString($user->getLogin(), $data[0]['Message']);
        $this->assertStringContainsString('ip-test', $data[0]['Message']);
        $this->assertStringContainsString(LogLevel::INFO, $data[0]['Niveau']);

        $result = $this->loggerService->deleteLog($logFile);
        $this->assertTrue($result);
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', false);
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $data = $result['data'];
        $this->assertStringContainsString($user->getLogin(), $data[0]['Message']);
        $this->assertStringContainsString('ip-test', $data[0]['Message']);
        $this->assertStringContainsString(LogLevel::WARNING, $data[0]['Niveau']);
    }

    /**
     * Test méthode logSwitchUser()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogSwitchUser(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $logFile = 'auth-' . date('Y-m-d') . '.log';
        $result = $this->loggerService->deleteLog($logFile);
        $this->assertTrue($result);
        $this->loggerService->logSwitchUser($user1->getLogin(), $user2->getLogin());
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $data = $result['data'];
        $this->assertStringContainsString($user1->getLogin(), $data[0]['Message']);
        $this->assertStringContainsString($user2->getLogin(), $data[0]['Message']);
        $this->assertStringContainsString(LogLevel::WARNING, $data[0]['Niveau']);
    }

    /**
     * Test méthode logDoctrine()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogDoctrine(): void
    {
        $this->createUser();

        $logFile = 'doctrine-' . date('Y-m-d') . '.log';
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $data = $result['data'];
        $this->assertNotEmpty($data);
    }

    /**
     * Test méthode getAllFiles()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFiles(): void
    {
        $result = $this->loggerService->getAllFiles();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $data = $result[0];
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('path', $data);

        $result = $this->loggerService->getAllFiles(date('Y-m-d'));
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $data = $result[0];
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('path', $data);

        $result = $this->loggerService->getAllFiles('2000-01-01');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * test méthode loadLogFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoadLogFile(): void
    {
        $user = $this->createUser();

        $logFile = 'auth-' . date('Y-m-d') . '.log';
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', true);
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('taille', $result);
        $this->assertArrayHasKey('urlSaveSql', $result);
        $this->assertArrayHasKey('listLimit', $result);
        $this->assertIsArray($result['listLimit']);
        $this->assertNotEmpty($result['listLimit']);
        $this->assertArrayHasKey('translate', $result);
        $this->assertIsArray($result['translate']);
        $this->assertNotEmpty($result['translate']);
    }

    /**
     * test méthode deleteLog()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDeleteLog(): void
    {
        $user = $this->createUser();

        $logFile = 'auth-' . date('Y-m-d') . '.log';
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', true);
        $result = $this->loggerService->deleteLog($logFile);
        $this->assertTrue($result);

        $result = $this->loggerService->deleteLog('toto.log');
        $this->assertFalse($result);
    }

    /**
     * Test méthode getPathFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetPathFile(): void
    {
        $user = $this->createUser();

        $logFile = 'auth-' . date('Y-m-d') . '.log';
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', true);
        $result = $this->loggerService->getPathFile($logFile);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);

        $result = $this->loggerService->getPathFile('toto.log');
        $this->assertIsString($result);
        $this->assertEmpty($result);
    }
}