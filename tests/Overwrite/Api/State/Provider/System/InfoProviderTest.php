<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de l'exemple de surcharge du provider de GET /api/v2/info
 */

namespace App\Tests\Overwrite\Api\State\Provider\System;

use App\Api\V2\Resource\System\Info;
use App\Overwrite\Api\State\Provider\System\InfoProvider;
use App\Tests\Api\V2\AppApiV2TestCase;

class InfoProviderTest extends AppApiV2TestCase
{
    /**
     * L'opération info_get reçoit le provider de l'exemple, qui remplit extra
     * @return void
     */
    public function testOverwrittenProvider(): void
    {
        $operation = $this->container
            ->get('api_platform.metadata.resource.metadata_collection_factory')
            ->create(Info::class)
            ->getOperation('info_get');
        $this->assertSame(InfoProvider::class, $operation->getProvider());

        $response = $this->requestV2('GET', '/info');
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceItemJsonSchema(Info::class);
        $this->assertSame(['overwritten' => true], $this->decode($response)['extra']);
    }
}
