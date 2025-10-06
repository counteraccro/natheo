<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test Service TranslateService
 */
namespace App\Tests\Service\Admin\System;

use App\Service\Admin\System\TranslateService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class TranslateServiceTest extends AppWebTestCase
{
    /**
     * @var TranslateService|mixed|object|Container|null
     */
    private TranslateService $translateService;

    public function setUp(): void
    {
        parent::setUp();
        $this->translateService = $this->container->get(TranslateService::class);
    }

    /**
     * test méthode getListLanguages()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListLanguages()
    {
        $result = $this->translateService->getListLanguages();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /**
     * test méthode getTranslationFilesByLanguage()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetTranslationFilesByLanguage()
    {
        $fileName = $this->createTranslateFile();
        $result = $this->translateService->getTranslationFilesByLanguage('fr');
        $this->assertIsArray($result);
        $this->removeTranslateFile($fileName);
    }

    /**
     * Test de la méthode getTranslationFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetTranslationFile(): void
    {
        $data = [
            'unit-test.key.1' => self::getFaker()->text(),
            'unit-test.key.2' => self::getFaker()->text(),
            'unit-test.key.3' => self::getFaker()->text(),
            'unit-test.key.4' => self::getFaker()->text(),
            'unit-test.key.5' => self::getFaker()->text(),
        ];

        $path = $this->createTranslateFile(customData: $data);
        $tmp = explode(DIRECTORY_SEPARATOR, $path);
        $fileName = $tmp[array_key_last($tmp)];
        $result = $this->translateService->getTranslationFile($fileName);
        $this->assertIsArray($result);
        $this->assertCount(5, $result);
        $keys = array_keys($data);
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($data[$key], $result[$key]);
        }
        $this->removeTranslateFile($path);
    }

    /**
     * Test méthode updateTranslateFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateTranslateFile(): void
    {
        $data = [
            'unit-test.key.1' => self::getFaker()->text(),
            'unit-test.key.2' => self::getFaker()->text(),
            'unit-test.key.3' => self::getFaker()->text(),
            'unit-test.key.4' => self::getFaker()->text(),
            'unit-test.key.5' => self::getFaker()->text(),
        ];

        $path = $this->createTranslateFile(customData: $data);
        $tmp = explode(DIRECTORY_SEPARATOR, $path);
        $fileName = $tmp[array_key_last($tmp)];

        $this->translateService->updateTranslateFile($fileName, [
            ['key' => 'unit-test.key.1', 'value' => 'mise à jour'],
        ]);
        $result = $this->translateService->getTranslationFile($fileName);
        $this->assertCount(5, $result);
        $keys = array_keys($data);
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $result);
        }
        $this->assertEquals('mise à jour', $result['unit-test.key.1']);
        $this->removeTranslateFile($path);
    }
}
