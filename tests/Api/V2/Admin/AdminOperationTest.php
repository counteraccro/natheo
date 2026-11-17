<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la sécurité des opérations d'administration, sur la ressource de test AdminProbe
 */

namespace App\Tests\Api\V2\Admin;

use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Tests\Api\V2\AppApiV2TestCase;
use App\Tests\Api\V2\Resource\Admin\AdminProbe;
use Symfony\Component\Routing\RouterInterface;

class AdminOperationTest extends AppApiV2TestCase
{
    /**
     * Chemin de l'élément existant
     * @var string
     */
    private const string ITEM = '/admin/probes/' . AdminProbe::EXISTING_ID;

    /**
     * Sans session ouverte par un token back-office : refusé, quel que soit le token
     * @return void
     */
    public function testSessionAndScopeRequired(): void
    {
        $user = $this->createUserAdmin(['disabled' => false, 'anonymous' => false]);
        $publicSession = $this->sessionHeaders($user, 'ROLE_ADMIN_API');
        $adminSession = $this->adminSessionHeaders($user);

        $this->assertProblem($this->requestV2('GET', '/admin/probes', self::HEADER_NONE), 401);
        $this->assertProblem($this->requestV2('GET', '/admin/probes', self::HEADER_ADMIN), 403);
        $this->assertProblem($this->requestV2('GET', '/admin/probes', headers: $this->backOfficeTokenHeaders()), 403);
        $this->assertProblem($this->requestV2('GET', '/admin/probes', headers: $publicSession), 403);

        $response = $this->requestV2('GET', '/admin/probes', headers: $adminSession);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminProbe::class);
    }

    /**
     * Droits de l'utilisateur, quel que soit le rôle du token back-office : rôle du module, condition propre
     * à l'opération
     * @return void
     */
    public function testRoles(): void
    {
        $contributor = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $contributorHeaders = $this->adminSessionHeaders($contributor);
        $admin = $this->adminSessionHeaders($this->createUserAdmin(['disabled' => false, 'anonymous' => false]));
        $member = $this->adminSessionHeaders($this->createUser(['disabled' => false, 'anonymous' => false]));

        $this->assertEquals(200, $this->requestV2('GET', self::ITEM, headers: $contributorHeaders)->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminProbe::class);
        $this->assertEquals(
            200,
            $this->requestV2('PATCH', self::ITEM, body: ['name' => 'x'], headers: $contributorHeaders)->getStatusCode(),
        );
        $this->assertProblem($this->requestV2('DELETE', self::ITEM, headers: $contributorHeaders), 403);

        $this->assertEquals(204, $this->requestV2('DELETE', self::ITEM, headers: $admin)->getStatusCode());
        $this->assertProblem($this->requestV2('GET', self::ITEM, headers: $member), 403);
    }

    /**
     * frontAllowed : une session ouverte par un token public est acceptée, toujours croisée avec les droits
     * @return void
     */
    public function testFrontAllowed(): void
    {
        $contributor = $this->sessionHeaders(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
            'ROLE_WRITE_API',
        );
        $member = $this->sessionHeaders(
            $this->createUser(['disabled' => false, 'anonymous' => false]),
            'ROLE_WRITE_API',
        );

        $response = $this->requestV2('POST', '/admin/probes', body: ['name' => 'x'], headers: $contributor);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertProblem($this->requestV2('POST', '/admin/probes', body: ['name' => 'x'], headers: $member), 403);
        $this->assertProblem($this->requestV2('POST', '/admin/probes', self::HEADER_WRITE, ['name' => 'x']), 403);
    }

    /**
     * Le name d'une opération d'administration est sa route : aucune route du back-office ne doit le porter
     * @return void
     */
    public function testAdminOperationRoutes(): void
    {
        $router = $this->container->get(RouterInterface::class);
        $nameFactory = $this->container->get('api_platform.metadata.resource.name_collection_factory');
        $metadataFactory = $this->container->get('api_platform.metadata.resource.metadata_collection_factory');
        $prefix = $this->container->getParameter('app.api_v2.prefix') . OperationSecurityMetadataFactory::ADMIN_PREFIX;

        $count = 0;
        foreach ($nameFactory->create() as $resourceClass) {
            foreach ($metadataFactory->create($resourceClass) as $resource) {
                foreach ($resource->getOperations() ?? [] as $name => $operation) {
                    if (!isset($operation->getExtraProperties()[OperationSecurityMetadataFactory::MODULE])) {
                        continue;
                    }
                    $count++;
                    $this->assertStringStartsWith(
                        $prefix,
                        $router->getRouteCollection()->get($name)?->getPath(),
                        $name,
                    );
                }
            }
        }
        $this->assertGreaterThan(0, $count);
    }
}
