<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des en-têtes de dépréciation de l'API v1
 */

namespace App\Tests\EventListener;

use App\EventListener\ApiV1DeprecationListener;
use App\Tests\Api\V2\AppApiV2TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiV1DeprecationListenerTest extends AppApiV2TestCase
{
    /**
     * Réponse v1 en succès : Deprecation et Link, sans Sunset tant qu'aucune date n'est fixée
     * @return void
     */
    public function testSuccessResponseIsDeprecated(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('api_authentication_auth', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDeprecationHeaders($response);
        $this->assertFalse($response->headers->has('Sunset'));
    }

    /**
     * Erreur du pare-feu sur la v1 : en-têtes présents
     * @return void
     */
    public function testFirewallErrorIsDeprecated(): void
    {
        $this->client->request(
            'GET',
            $this->router->generate('api_authentication_auth', ['api_version' => self::API_VERSION]),
            server: $this->getCustomHeaders(self::HEADER_WRONG),
        );
        $response = $this->client->getResponse();

        $this->assertSame(401, $response->getStatusCode());
        $this->assertDeprecationHeaders($response);
    }

    /**
     * Réponse v2 : aucun en-tête de dépréciation
     * @return void
     */
    public function testV2ResponseIsNotDeprecated(): void
    {
        $response = $this->requestV2('GET', '/info');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Deprecation'));
        $this->assertFalse($response->headers->has('Sunset'));
    }

    /**
     * Date de suppression fixée : Sunset au format HTTP ; Link existant conservé
     * @return void
     */
    public function testSunsetAndExistingLink(): void
    {
        $listener = new ApiV1DeprecationListener('v1', '2026-12-01', '2027-06-30', 'https://example.com/guide');
        $response = new Response();
        $response->headers->set('Link', '</api/v1/page/find?page=2>; rel="next"');

        $listener(
            new ResponseEvent(
                $this->client->getKernel(),
                Request::create('/api/v1/page/find'),
                HttpKernelInterface::MAIN_REQUEST,
                $response,
            ),
        );

        $this->assertSame('@1796083200', $response->headers->get('Deprecation'));
        $this->assertSame('Wed, 30 Jun 2027 00:00:00 GMT', $response->headers->get('Sunset'));
        $this->assertSame(
            [
                '</api/v1/page/find?page=2>; rel="next"',
                '<https://example.com/guide>; rel="deprecation"; type="text/html"',
            ],
            $response->headers->all('link'),
        );
    }

    /**
     * En-têtes Deprecation et Link construits depuis les paramètres
     * @param Response $response
     * @return void
     */
    private function assertDeprecationHeaders(Response $response): void
    {
        $deprecatedAt = new \DateTimeImmutable($this->container->getParameter('app.api_v1.deprecated_at') . ' UTC');
        $this->assertSame('@' . $deprecatedAt->getTimestamp(), $response->headers->get('Deprecation'));
        $this->assertSame(
            '<' .
                $this->container->getParameter('app.api_v1.migration_guide_url') .
                '>; rel="deprecation"; type="text/html"',
            $response->headers->get('Link'),
        );
    }
}
