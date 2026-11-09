<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Classe de base des tests de l'API v2 (API Platform)
 */

namespace App\Tests\Api\V2;

use ApiPlatform\Test\ApiTestAssertionsTrait;
use ApiPlatform\Test\Response as ApiResponse;
use App\Tests\Controller\Api\AppApiTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AppApiV2TestCase extends AppApiTestCase
{
    // Assertions d'API Platform (schémas JSON des ressources) appliquées au client de WebTestCase
    use ApiTestAssertionsTrait;

    /**
     * Client de la dernière requête, lu par les assertions statiques d'API Platform
     * @var KernelBrowser|null
     */
    private static ?KernelBrowser $lastClient = null;

    /**
     * Préfixe des routes de l'API v2
     * @var string
     */
    protected const string PREFIX = '/api/v2';

    /**
     * Code pour générer un header sans token
     * @var int
     */
    protected const int HEADER_NONE = 0;

    /**
     * Envoie une requête à l'API v2 et retourne la réponse
     * @param string $method
     * @param string $path chemin relatif au préfixe /api/v2
     * @param int $typeHeader
     * @param array|null $body
     * @param string $accept
     * @param array<string, string> $headers en-têtes supplémentaires au format $_SERVER (HTTP_…)
     * @return Response
     */
    protected function requestV2(
        string $method,
        string $path,
        int $typeHeader = self::HEADER_READ,
        ?array $body = null,
        string $accept = 'application/ld+json',
        array $headers = [],
    ): Response {
        $server = $typeHeader === self::HEADER_NONE ? [] : $this->getCustomHeaders($typeHeader);
        // Request::create() envoie « en-us » par défaut : sans en-tête, la langue par défaut du site s'applique
        $server = [...$server, 'HTTP_ACCEPT_LANGUAGE' => '', ...$headers];
        $server['HTTP_ACCEPT'] = $accept;
        unset($server['HTTP_Accept'], $server['HTTP_Content-Type']);
        if ($body !== null) {
            $server['CONTENT_TYPE'] = 'application/ld+json';
        }

        $this->client->request(
            $method,
            self::PREFIX . $path,
            server: $server,
            content: $body === null ? null : json_encode($body),
        );
        self::$lastClient = $this->client;
        return $this->client->getResponse();
    }

    /**
     * Décode le contenu JSON d'une réponse
     * @param Response $response
     * @return array
     */
    protected function decode(Response $response): array
    {
        $this->assertJson((string) $response->getContent());
        return json_decode((string) $response->getContent(), true);
    }

    /**
     * Vérifie qu'une réponse est une erreur problem+json avec le statut attendu
     * @param Response $response
     * @param int $status
     * @return array
     */
    protected function assertProblem(Response $response, int $status): array
    {
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertStringStartsWith('application/problem+json', (string) $response->headers->get('Content-Type'));
        $content = $this->decode($response);
        $this->assertEquals($status, $content['status']);
        $this->assertArrayHasKey('detail', $content);
        return $content;
    }

    /**
     * Dernière réponse du client, au format attendu par ApiTestAssertionsTrait
     * @return ResponseInterface
     */
    private static function getHttpResponse(): ResponseInterface
    {
        $client = self::$lastClient ?? self::fail('Aucune requête envoyée avec requestV2()');
        return new ApiResponse($client->getResponse(), $client->getInternalResponse(), []);
    }
}
