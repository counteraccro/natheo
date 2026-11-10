<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Classe de base des tests de l'API v2 (API Platform)
 */

namespace App\Tests\Api\V2;

use ApiPlatform\JsonSchema\BackwardCompatibleSchemaFactory;
use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\Test\Constraint\MatchesJsonSchema;
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
            $server['CONTENT_TYPE'] = $method === 'PATCH' ? 'application/merge-patch+json' : 'application/ld+json';
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

    /**
     * Vérifie la dernière réponse avec le schéma JSON-LD d'une opération d'item de la ressource
     * @param string $resourceClass
     * @param string|null $operationName opération d'item par défaut si null
     * @return void
     */
    protected function assertMatchesResourceItemJsonSchema(string $resourceClass, ?string $operationName = null): void
    {
        $this->assertMatchesResourceJsonSchema($resourceClass, $operationName, false);
    }

    /**
     * Vérifie la dernière réponse avec le schéma JSON-LD d'une opération de collection de la ressource
     * @param string $resourceClass
     * @param string|null $operationName opération de collection par défaut si null
     * @return void
     */
    protected function assertMatchesResourceCollectionJsonSchema(string $resourceClass, ?string $operationName = null): void
    {
        $this->assertMatchesResourceJsonSchema($resourceClass, $operationName, true);
    }

    /**
     * Construit le schéma de sortie de l'opération, comme les assertions d'API Platform, et valide la réponse
     * @param string $resourceClass
     * @param string|null $operationName
     * @param bool $collection
     * @return void
     */
    private function assertMatchesResourceJsonSchema(string $resourceClass, ?string $operationName, bool $collection): void
    {
        $operation = $this->container
            ->get('api_platform.metadata.resource.metadata_collection_factory')
            ->create($resourceClass)
            ->getOperation($operationName, $collection);
        $schema = $this->container
            ->get('api_platform.json_schema.schema_factory')
            ->buildSchema(
                $resourceClass,
                'jsonld',
                Schema::TYPE_OUTPUT,
                $operation,
                null,
                ($operation->getNormalizationContext() ?? []) + [BackwardCompatibleSchemaFactory::SCHEMA_DRAFT4_VERSION => true],
            );

        $this->assertThat($this->decode($this->client->getResponse()), new MatchesJsonSchema($schema->getArrayCopy()));
    }
}
