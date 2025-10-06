<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test service installation
 */

namespace App\Tests\Service\Installation;

use App\Entity\Admin\System\User;
use App\Service\Installation\InstallationService;
use App\Tests\AppWebTestCase;
use App\Utils\Global\EnvFile;
use App\Utils\Installation\InstallationConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class InstallationServiceTest extends AppWebTestCase
{
    /**
     * @var InstallationService|mixed|object|Container|null
     */
    private InstallationService $installationService;

    public function setUp(): void
    {
        parent::setUp();
        $this->installationService = $this->container->get(InstallationService::class);
    }

    /**
     * Test méthode getValueByKeyInEnvFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetValueByKeyInEnvFile(): void
    {
        $result = $this->installationService->getValueByKeyInEnvFile(EnvFile::KEY_APP_ENV);
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringContainsString(EnvFile::KEY_APP_ENV, $result);
    }

    /**
     * Test méthode updateValueByKeyInEnvFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateValueByKeyInEnvFile(): void
    {
        $this->installationService->updateValueByKeyInEnvFile('APP_DEBUG', 'APP_DEBUG=3');

        $result = $this->installationService->getValueByKeyInEnvFile('APP_DEBUG');
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertEquals('APP_DEBUG=3', $result);

        $this->installationService->updateValueByKeyInEnvFile('APP_DEBUG', 'APP_DEBUG=1');
    }

    /**
     * Test méthode generateSecret()
     * @return void
     */
    public function testGenerateSecret(): void
    {
        $result = $this->installationService->generateSecret();
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertStringContainsString(EnvFile::KEY_APP_SECRET, $result);
    }

    /**
     * Test méthode formatDatabaseUrlForEnvFile()
     * @return void
     */
    public function testFormatDatabaseUrlForEnvFile(): void
    {
        $data = [
            'type' => 'mysql',
            'login' => 'unit-test',
            'password' => 'unit-test-password',
            'ip' => '127.0.0.1',
            'port' => '3306',
            'bdd_name' => 'unit-test-bdd',
            'charset' => 'utf8mb4',
            'version' => '1.0',
        ];

        $result = $this->installationService->formatDatabaseUrlForEnvFile($data, 'toto');
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertEquals(
            'DATABASE_URL="' .
                $data['type'] .
                '://' .
                $data['login'] .
                ':' .
                $data['password'] .
                '@' .
                $data['ip'] .
                ':' .
                $data['port'] .
                '"',
            $result,
        );

        $result = $this->installationService->formatDatabaseUrlForEnvFile(
            $data,
            InstallationConst::OPTION_DATABASE_URL_CREATE_DATABASE,
        );
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertEquals(
            'DATABASE_URL="' .
                $data['type'] .
                '://' .
                $data['login'] .
                ':' .
                $data['password'] .
                '@' .
                $data['ip'] .
                ':' .
                $data['port'] .
                '/' .
                $data['bdd_name'] .
                '?serverVersion=' .
                $data['version'] .
                '&charset=' .
                $data['charset'] .
                '"',
            $result,
        );
    }

    /**
     * Test méthode getDatabaseUrl()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetDatabaseUrl(): void
    {
        $result = $this->installationService->getDatabaseUrl();
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('login', $result);
        $this->assertArrayHasKey('password', $result);
        $this->assertArrayHasKey('ip', $result);
        $this->assertArrayHasKey('port', $result);
        $this->assertArrayHasKey('bdd_name', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('charset', $result);
    }

    /**
     * Test méthode checkSchema()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCheckSchema(): void
    {
        $result = $this->installationService->checkSchema();
        $this->assertTrue($result);
    }

    /**
     * Test méthode checkDataExiste()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCheckDataExiste(): void
    {
        $result = $this->installationService->checkDataExiste(User::class);
        $this->assertFalse($result);

        $this->createUser();
        $result = $this->installationService->checkDataExiste(User::class);
        $this->assertTrue($result);
    }

    /**
     * Test méthode createNotificationFondateur()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateNotificationFondateur(): void
    {
        $user = $this->createUser();
        $this->installationService->createNotificationFondateur();

        $verif = $this->em->getRepository(User::class)->find($user->getId());
        $this->assertCount(1, $verif->getNotifications()->toArray());
    }

    /**
     * Test méthode createUser()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateUser(): void
    {
        $data = [
            'email' => self::getFaker()->email(),
            'password' => self::getFaker()->password(),
            'login' => self::getFaker()->username(),
        ];
        $result = $this->installationService->createUser($data);
        $this->assertTrue($result['success']);
    }
}
