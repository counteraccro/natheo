<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests du socle de l'API v2 : sécurité, formats, erreurs et documentation
 */

namespace App\Tests\Api\V2;

use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\Api\V2\Resource\Probe;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiPlatformTest extends AppApiV2TestCase
{
    /**
     * Sans token, avec un token invalide : 401 avec WWW-Authenticate
     * @return void
     */
    public function testAuthenticationRequired(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $response = $this->requestV2('GET', '/probes', self::HEADER_NONE);
        $content = $this->assertProblem($response, 401);
        $this->assertEquals('Bearer', $response->headers->get('WWW-Authenticate'));
        $this->assertEquals(
            $translator->trans('api_errors.authentication.required', domain: 'api_errors'),
            $content['detail'],
        );

        $response = $this->requestV2('GET', '/probes', self::HEADER_WRONG);
        $content = $this->assertProblem($response, 401);
        $this->assertEquals('Bearer error="invalid_token"', $response->headers->get('WWW-Authenticate'));
        $this->assertStringContainsString(
            $translator->trans('api_errors.authentication.failure', domain: 'api_errors'),
            $content['detail'],
        );
    }

    /**
     * Rôle insuffisant : 403, rôle suffisant : opération réalisée
     * @return void
     */
    public function testRolesByOperation(): void
    {
        $this->assertEquals(200, $this->requestV2('GET', '/probes', self::HEADER_READ)->getStatusCode());

        $this->assertProblem($this->requestV2('POST', '/probes', self::HEADER_READ, ['name' => 'test']), 403);
        $this->assertEquals(
            201,
            $this->requestV2('POST', '/probes', self::HEADER_WRITE, ['name' => 'test'])->getStatusCode(),
        );

        $path = '/probes/' . Probe::EXISTING_ID;
        $this->assertProblem($this->requestV2('DELETE', $path, self::HEADER_WRITE), 403);
        $this->assertEquals(204, $this->requestV2('DELETE', $path, self::HEADER_ADMIN)->getStatusCode());
    }

    /**
     * API fermée : 403 avec ou sans token, documentation masquée
     * @return void
     */
    public function testClosedApi(): void
    {
        $this->container->get(OptionSystemService::class)->saveValueByKee(OptionSystem::OS_OPEN_API->value, '0');

        $translator = $this->container->get(TranslatorInterface::class);
        $content = $this->assertProblem($this->requestV2('GET', '/probes', self::HEADER_READ), 403);
        $this->assertStringContainsString(
            $translator->trans('api_errors.api.not.open', domain: 'api_errors'),
            $content['detail'],
        );
        $this->assertProblem($this->requestV2('GET', '/probes', self::HEADER_NONE), 403);
        foreach (
            ['/errors/404', '/validation_errors/8e179f1b-97aa-4560-a02f-2a8b42e49df7', '/.well-known/genid/1']
            as $path
        ) {
            $this->assertEquals(403, $this->requestV2('GET', $path, self::HEADER_NONE)->getStatusCode(), $path);
        }

        $this->assertEquals(404, $this->requestV2('GET', '/docs.jsonopenapi', self::HEADER_NONE)->getStatusCode());
        $this->assertEquals(404, $this->requestV2('GET', '/', self::HEADER_NONE)->getStatusCode());
    }

    /**
     * JSON-LD / Hydra par défaut, JSON simple sur demande
     * @return void
     */
    public function testFormats(): void
    {
        $response = $this->requestV2('GET', '/probes', accept: '*/*');
        $this->assertStringStartsWith('application/ld+json', (string) $response->headers->get('Content-Type'));
        $content = $this->decode($response);
        $this->assertEquals('/api/v2/contexts/Probe', $content['@context']);
        $this->assertEquals('Collection', $content['@type']);
        $this->assertEquals(1, $content['totalItems']);
        $this->assertEquals('/api/v2/probes/' . Probe::EXISTING_ID, $content['member'][0]['@id']);

        $response = $this->requestV2('GET', '/probes/' . Probe::EXISTING_ID, accept: 'application/json');
        $this->assertStringStartsWith('application/json', (string) $response->headers->get('Content-Type'));
        $this->assertEquals(['id' => Probe::EXISTING_ID, 'name' => 'probe'], $this->decode($response));
    }

    /**
     * Erreurs au format problem+json avec un titre traduit : ressource absente, validation
     * @return void
     */
    public function testErrors(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $content = $this->assertProblem($this->requestV2('GET', '/probes/99'), 404);
        $this->assertEquals($translator->trans('api_errors.title.404', domain: 'api_errors'), $content['title']);
        $this->assertProblem($this->requestV2('GET', '/probes/99', accept: 'application/json'), 404);

        $content = $this->assertProblem($this->requestV2('POST', '/probes', self::HEADER_WRITE, ['name' => '']), 422);
        $this->assertEquals('name', $content['violations'][0]['propertyPath']);
        $this->assertEquals($translator->trans('api_errors.title.422', domain: 'api_errors'), $content['title']);
    }

    /**
     * Documentation OpenAPI publique tant que l'API est ouverte
     * @return void
     */
    public function testDocumentation(): void
    {
        $response = $this->requestV2(
            'GET',
            '/docs.jsonopenapi',
            self::HEADER_NONE,
            accept: 'application/vnd.openapi+json',
        );
        $this->assertEquals(200, $response->getStatusCode());
        $content = $this->decode($response);
        $this->assertArrayHasKey('/api/v2/info', $content['paths']);
        $this->assertArrayHasKey('Bearer', $content['components']['securitySchemes']);

        foreach ($content['paths'] as $path => $operations) {
            foreach ($operations as $method => $operation) {
                if (!is_array($operation) || ($operation['tags'] ?? []) === ['Probe']) {
                    continue;
                }
                $this->assertDoesNotMatchRegularExpression(
                    '/^(Retrieves|Creates|Updates|Replaces|Deletes) /',
                    $operation['summary'],
                    $method . ' ' . $path . ' : résumé OpenAPI manquant',
                );
            }
        }

        $response = $this->requestV2('GET', '/docs', self::HEADER_NONE, accept: 'text/html');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('swagger', strtolower((string) $response->getContent()));
    }
}
