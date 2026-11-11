<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource Info de l'API v2
 */

namespace App\Tests\Api\V2\System;

use App\Api\V2\Resource\System\Info;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Api\System\ApiInfoService;
use App\Tests\Api\V2\AppApiV2TestCase;

class InfoTest extends AppApiV2TestCase
{
    /**
     * Test GET /api/v2/info
     * @return void
     */
    public function testGetInfo(): void
    {
        $this->assertProblem($this->requestV2('GET', '/info', self::HEADER_NONE), 401);

        $response = $this->requestV2('GET', '/info');
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceItemJsonSchema(Info::class);
        $this->assertNotContains('Accept-Language', $response->getVary());
        $this->assertNull($response->headers->get('Content-Language'));
        $content = $this->decode($response);

        $siteName = $this->container->get(OptionSystemService::class)->getValueByKey(OptionSystem::OS_SITE_NAME->value);
        $this->assertEquals('Info', $content['@type']);
        $this->assertEquals('/api/v2/info', $content['@id']);
        $this->assertEquals($siteName, $content['siteName']);
        $this->assertEquals($this->container->getParameter('app.version'), $content['natheoVersion']);
        $this->assertEquals(ApiInfoService::API_VERSION, $content['apiVersion']);
    }
}
