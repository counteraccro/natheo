<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource OptionSystem de l'API v2
 */

namespace App\Tests\Api\V2\System;

use App\Api\V2\Resource\System\OptionSystem;
use App\Enum\Admin\System\Options\OptionSystem as OptionSystemKey;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Api\System\ApiOptionSystemService;
use App\Tests\Api\V2\AppApiV2TestCase;

class OptionSystemTest extends AppApiV2TestCase
{
    /**
     * Test GET /api/v2/options_systems
     * @return void
     */
    public function testGetCollection(): void
    {
        $this->assertProblem($this->requestV2('GET', '/options_systems', self::HEADER_NONE), 401);

        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_SITE_NAME->value, 'unit-test_site');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_OPEN_COMMENT->value, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_FRONT_ROBOT_NO_INDEX->value, '0');

        $content = $this->decode($this->requestV2('GET', '/options_systems'));
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceCollectionJsonSchema(OptionSystem::class);

        $whiteList = $this->container->get(ApiOptionSystemService::class)->getWhiteListeOptionSystem();
        $this->assertCount(count($whiteList), $content['member']);
        $this->assertArrayNotHasKey('view', $content);

        $values = array_column($content['member'], 'value', 'key');
        $this->assertSame('unit-test_site', $values['site_name']);
        $this->assertTrue($values['open_comment']);
        $this->assertFalse($values['front_robot_no_index']);
        $this->assertArrayNotHasKey('notification', $values);
        $this->assertEquals('/api/v2/options_systems/site_name', $content['member'][0]['@id']);
    }

    /**
     * Test GET /api/v2/options_systems/{key}
     * @return void
     */
    public function testGetItem(): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(OptionSystemKey::OS_SITE_NAME->value, 'unit-test_site');

        $content = $this->decode($this->requestV2('GET', '/options_systems/site_name'));
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceItemJsonSchema(OptionSystem::class);
        $this->assertEquals('OptionSystem', $content['@type']);
        $this->assertEquals('site_name', $content['key']);
        $this->assertEquals('unit-test_site', $content['value']);

        $detail = $this->translator->trans('api_errors.find.option.system.not.found', domain: 'api_errors');
        // Option existante non exposée, clé v1, clé inconnue
        foreach (['notification', 'OS_SITE_NAME', 'azerty'] as $key) {
            $content = $this->assertProblem($this->requestV2('GET', '/options_systems/' . $key), 404);
            $this->assertEquals($detail, $content['detail']);
        }
    }
}
