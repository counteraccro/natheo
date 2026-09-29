<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test service OptionConfig
 */
namespace App\Tests\Service\Admin\System;

use App\Service\Admin\System\OptionConfigService;
use App\Tests\AppWebTestCase;

class OptionConfigServiceTest extends AppWebTestCase
{
    /**
     * @var OptionConfigService
     */
    private OptionConfigService $optionConfigService;

    public function setUp(): void
    {
        parent::setUp();
        $this->optionConfigService = $this->container->get(OptionConfigService::class);
    }

    /**
     * Test méthode findByKey()
     * @return void
     */
    public function testFindByKey(): void
    {
        $config = [
            'options_test' => [
                'categorie' => ['options' => ['OS_TEST' => ['type' => 'boolean']]],
            ],
        ];
        $this->assertEquals(['type' => 'boolean'], $this->optionConfigService->findByKey($config, 'OS_TEST'));
        $this->assertNull($this->optionConfigService->findByKey($config, 'OS_KEY_NOT_EXIST'));
        $this->assertNull($this->optionConfigService->findByKey([], 'OS_TEST'));
    }

    /**
     * Test méthode isEditable()
     * @return void
     */
    public function testIsEditable(): void
    {
        $this->assertTrue($this->optionConfigService->isEditable(['type' => 'text']));
        $this->assertFalse($this->optionConfigService->isEditable(['type' => 'text', 'disabled' => true]));
        $this->assertFalse($this->optionConfigService->isEditable(null));
    }

    /**
     * Test méthode isValidValue()
     * @return void
     */
    public function testIsValidValue(): void
    {
        $boolean = ['type' => 'boolean'];
        $this->assertTrue($this->optionConfigService->isValidValue($boolean, '1'));
        $this->assertFalse($this->optionConfigService->isValidValue($boolean, 'abc'));

        $select = ['type' => 'select', 'list_value' => 'fr:global.fr|en:global.en'];
        $this->assertTrue($this->optionConfigService->isValidValue($select, 'en'));
        $this->assertFalse($this->optionConfigService->isValidValue($select, 'xx'));

        $required = ['type' => 'text', 'required' => true];
        $this->assertTrue($this->optionConfigService->isValidValue($required, 'Natheo'));
        $this->assertFalse($this->optionConfigService->isValidValue($required, '  '));

        $email = ['type' => 'text', 'validation' => 'email'];
        $this->assertTrue($this->optionConfigService->isValidValue($email, 'support@natheo.fr'));
        $this->assertFalse($this->optionConfigService->isValidValue($email, 'not-an-email'));

        $url = ['type' => 'text', 'validation' => 'url'];
        $this->assertTrue($this->optionConfigService->isValidValue($url, ''));
        $this->assertTrue($this->optionConfigService->isValidValue($url, 'https://github.com/counteraccro/natheo'));
        $this->assertFalse($this->optionConfigService->isValidValue($url, 'javascript:alert(1)'));
    }
}
