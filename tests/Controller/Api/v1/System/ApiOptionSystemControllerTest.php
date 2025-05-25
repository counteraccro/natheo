<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test API option system
 */

namespace Controller\Api\v1\System;

use App\Service\Admin\System\OptionSystemService;
use App\Service\Api\System\ApiOptionSystemService;
use App\Tests\Controller\Api\AppApiTestCase;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiOptionSystemControllerTest extends AppApiTestCase
{
    /**
     * Test méthode listing()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testListing(): void
    {
        $this->client->request('GET', $this->router->generate('api_options_systems_listing', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders()
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('data', $content);
        $this->assertIsArray($content['data']);

        /** @var ApiOptionSystemService $apiOptionSystemService */
        $apiOptionSystemService = $this->container->get(ApiOptionSystemService::class);
        foreach ($content['data'] as $optionSystem => $value) {
            $this->assertTrue(in_array($optionSystem, $apiOptionSystemService->getWhiteListeOptionSystem()));
        }
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetByKey(): void
    {
        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $translator = $this->container->get(TranslatorInterface::class);

        $optionSystemService->saveValueByKee(OptionSystemKey::OS_SITE_NAME, 'unit-test_site');

        $this->client->request('GET', $this->router->generate('api_options_systems_get_by_key', ['api_version' => self::API_VERSION, 'key' => OptionSystemKey::OS_SITE_NAME]),
            server: $this->getCustomHeaders()
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('data', $content);
        $this->assertIsArray($content['data']);
        $this->assertArrayHasKey('key', $content['data']);
        $this->assertEquals(OptionSystemKey::OS_SITE_NAME, $content['data']['key']);
        $this->assertArrayHasKey('value', $content['data']);
        $this->assertEquals('unit-test_site', $content['data']['value']);

        // Clé non authorisé
        $this->client->request('GET', $this->router->generate('api_options_systems_get_by_key', ['api_version' => self::API_VERSION, 'key' => OptionSystemKey::OS_NOTIFICATION]),
            server: $this->getCustomHeaders()
        );
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertIsArray($content['errors']);
        $this->assertStringContainsString($translator->trans('api_errors.find.option.system.not.found', domain: 'api_errors'), $content['errors'][0]);

        // Clé qui n'existe pas
        $this->client->request('GET', $this->router->generate('api_options_systems_get_by_key', ['api_version' => self::API_VERSION, 'key' => 'AZERTY']),
            server: $this->getCustomHeaders()
        );
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertIsArray($content['errors']);
        $this->assertStringContainsString($translator->trans('api_errors.find.option.system.not.found', domain: 'api_errors'), $content['errors'][0]);
    }
}