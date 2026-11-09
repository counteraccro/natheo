<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Classe de base des tests de l'API v2 (API Platform)
 */

namespace App\Tests\Api\v2;

use App\Tests\Controller\Api\AppApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class AppApiV2TestCase extends AppApiTestCase
{
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
     * @return Response
     */
    protected function requestV2(
        string $method,
        string $path,
        int $typeHeader = self::HEADER_READ,
        ?array $body = null,
        string $accept = 'application/ld+json',
    ): Response {
        $server = $typeHeader === self::HEADER_NONE ? [] : $this->getCustomHeaders($typeHeader);
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
}
