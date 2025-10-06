<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Test service OptionSystem
 */
namespace App\Tests\Service\Admin\System;

use App\Entity\Admin\System\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class OptionSystemServiceTest extends AppWebTestCase
{
    /**
     * @var OptionSystemService|mixed|object|Container|null
     */
    private OptionSystemService $optionSystemService;

    public function setUp(): void
    {
        parent::setUp();
        $this->optionSystemService = $this->container->get(OptionSystemService::class);
    }

    /**
     * test méthode getAll()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAll(): void
    {
        $result = $this->optionSystemService->getAll();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * test méthode getByKey
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetByKey(): void
    {
        $result = $this->optionSystemService->getByKey(OptionSystemKey::OS_SITE_NAME);
        $this->assertInstanceOf(OptionSystem::class, $result);
        $this->assertEquals(OptionSystemKey::OS_SITE_NAME, $result->getKey());
        $this->assertEquals('Nathéo CMS', $result->getValue());

        $result = $this->optionSystemService->getByKey(OptionSystemKey::OS_OPEN_SITE);
        $this->assertInstanceOf(OptionSystem::class, $result);
        $this->assertEquals(OptionSystemKey::OS_OPEN_SITE, $result->getKey());
        $this->assertEquals('1', $result->getValue());

        $result = $this->optionSystemService->getByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE);
        $this->assertInstanceOf(OptionSystem::class, $result);
        $this->assertEquals(OptionSystemKey::OS_DEFAULT_LANGUAGE, $result->getKey());
        $this->assertEquals('fr', $result->getValue());
    }

    /**
     * Test méthode getValueByKey()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetValueByKey(): void
    {
        $result = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_SITE_NAME);
        $this->assertNotNull($result);
        $this->assertEquals('Nathéo CMS', $result);

        $result = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_OPEN_SITE);
        $this->assertNotNull($result);
        $this->assertEquals('1', $result);

        $result = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE);
        $this->assertNotNull($result);
        $this->assertEquals('fr', $result);

        $result = $this->optionSystemService->getValueByKey('option-qui-n-exise-pas');
        $this->assertNull($result);
    }

    /**
     * Test méthode getOptionsSystemConfig()
     * @return void
     */
    public function testGetOptionsSystemConfig(): void
    {
        $result = $this->optionSystemService->getOptionsSystemConfig();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test méthode saveValueByKee()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSaveValueByKee(): void
    {
        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_THEME_SITE, 'edit-purple');
        $result = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_THEME_SITE);
        $this->assertNotNull($result);
        $this->assertEquals('edit-purple', $result);
    }

    /**
     * Test méthode canSendMailNotification()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCanSendMailNotification(): void
    {
        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_MAIL_NOTIFICATION, '1');
        $result = $this->optionSystemService->canSendMailNotification();
        $this->assertTrue($result);

        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_MAIL_NOTIFICATION, '0');
        $result = $this->optionSystemService->canSendMailNotification();
        $this->assertFalse($result);
    }

    /**
     * Test méthode canDelete()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCanDelete(): void
    {
        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_ALLOW_DELETE_DATA, '1');
        $result = $this->optionSystemService->canDelete();
        $this->assertTrue($result);

        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_ALLOW_DELETE_DATA, '0');
        $result = $this->optionSystemService->canDelete();
        $this->assertFalse($result);
    }

    /**
     * Test méthode canReplace()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCanReplace(): void
    {
        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_REPLACE_DELETE_USER, '1');
        $result = $this->optionSystemService->canReplace();
        $this->assertTrue($result);

        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_REPLACE_DELETE_USER, '0');
        $result = $this->optionSystemService->canReplace();
        $this->assertFalse($result);
    }

    /**
     * Test méthode canNotification()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCanNotification(): void
    {
        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '1');
        $result = $this->optionSystemService->canNotification();
        $this->assertTrue($result);

        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_NOTIFICATION, '0');
        $result = $this->optionSystemService->canNotification();
        $this->assertFalse($result);
    }
}
