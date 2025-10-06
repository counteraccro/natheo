<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test EnvFile
 */
namespace App\Tests\Utils\Global;

use App\Tests\AppWebTestCase;
use App\Utils\Global\EnvFile;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class EnvFileTest extends AppWebTestCase
{
    /**
     * @var mixed|object|Container|null|EnvFile
     */
    public EnvFile $envFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->envFile = $this->container->get(EnvFile::class);
    }

    /**
     * Test méthode getPathEnvFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetPathEnvFile(): void
    {
        $result = $this->envFile->getPathEnvFile();
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    /**
     * Test méthode getValueByKey()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetValueByKey(): void
    {
        $result = $this->envFile->getValueByKey('APP_DEBUG');
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertEquals('APP_DEBUG=1', $result);
    }

    /**
     * Test méthode updateValueByKey()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateValueByKey(): void
    {
        $this->envFile->updateValueByKey('APP_DEBUG', 'APP_DEBUG=0');
        $result = $this->envFile->getValueByKey('APP_DEBUG');
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertEquals('APP_DEBUG=0', $result);

        $this->envFile->updateValueByKey('APP_DEBUG', 'APP_DEBUG=1');
        $result = $this->envFile->getValueByKey('APP_DEBUG');
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertEquals('APP_DEBUG=1', $result);
    }
}
