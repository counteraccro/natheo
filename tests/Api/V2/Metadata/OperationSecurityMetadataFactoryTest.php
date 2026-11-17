<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test de la sécurité construite par OperationSecurityMetadataFactory
 */

namespace App\Tests\Api\V2\Metadata;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AdminModuleRegistry;
use PHPUnit\Framework\TestCase;

class OperationSecurityMetadataFactoryTest extends TestCase
{
    /**
     * Opérations publiques, partagées et d'administration
     * @return void
     */
    public function testSecurity(): void
    {
        $operations = $this->create([
            'public' => new Get(uriTemplate: '/pages/{id}', security: "is_granted('ROLE_READ_API')"),
            'public_none' => new Get(uriTemplate: '/info'),
            'shared' => new Get(
                uriTemplate: '/me',
                security: "is_granted('ROLE_READ_API')",
                extraProperties: [OperationSecurityMetadataFactory::SHARED => true],
            ),
            'admin_read' => new Get(
                uriTemplate: '/admin/tags/{id}',
                extraProperties: [OperationSecurityMetadataFactory::MODULE => 'tag'],
            ),
            'admin_write' => new Patch(
                uriTemplate: '/admin/tags/{id}',
                security: "is_granted('ROLE_SUPER_ADMIN')",
                extraProperties: [OperationSecurityMetadataFactory::MODULE => 'tag'],
            ),
            'admin_front' => new Patch(
                uriTemplate: '/admin/tags/{id}',
                extraProperties: [
                    OperationSecurityMetadataFactory::MODULE => 'tag',
                    OperationSecurityMetadataFactory::FRONT_ALLOWED => true,
                ],
            ),
        ]);

        $this->assertSame(
            "is_granted('ROLE_API_PUBLIC') and (is_granted('ROLE_READ_API'))",
            $operations['public']->getSecurity(),
        );
        $this->assertSame("is_granted('ROLE_API_PUBLIC')", $operations['public_none']->getSecurity());
        $this->assertSame("is_granted('ROLE_READ_API')", $operations['shared']->getSecurity());
        $this->assertSame(
            "is_granted('ROLE_API_BACK_OFFICE') and is_granted('ROLE_API_USER_SESSION') and " .
                "is_granted('ROLE_CONTRIBUTEUR')",
            $operations['admin_read']->getSecurity(),
        );
        $this->assertSame(
            "is_granted('ROLE_API_BACK_OFFICE') and is_granted('ROLE_API_USER_SESSION') and " .
                "is_granted('ROLE_CONTRIBUTEUR') and (is_granted('ROLE_SUPER_ADMIN'))",
            $operations['admin_write']->getSecurity(),
        );
        $this->assertSame(
            "is_granted('ROLE_API_USER_SESSION') and is_granted('ROLE_CONTRIBUTEUR')",
            $operations['admin_front']->getSecurity(),
        );
    }

    /**
     * Une adresse /admin/ sans module, un module hors /admin/ ou un module inconnu font échouer les métadonnées
     * @return void
     */
    public function testInvalidAdminOperations(): void
    {
        $cases = [
            new Get(uriTemplate: '/admin/tags/{id}'),
            new Get(uriTemplate: '/tags/{id}', extraProperties: [OperationSecurityMetadataFactory::MODULE => 'tag']),
            new Get(
                uriTemplate: '/admin/tags/{id}',
                extraProperties: [OperationSecurityMetadataFactory::MODULE => 'inconnu'],
            ),
        ];
        foreach ($cases as $operation) {
            try {
                $this->create(['op' => $operation]);
                $this->fail('LogicException attendue pour ' . $operation->getUriTemplate());
            } catch (\LogicException $e) {
                $this->assertStringContainsString('op', $e->getMessage());
            }
        }
    }

    /**
     * Applique le décorateur à une ressource portant les opérations
     * @param array<string, \ApiPlatform\Metadata\Operation> $operations
     * @return array<string, \ApiPlatform\Metadata\Operation>
     */
    private function create(array $operations): array
    {
        $named = [];
        foreach ($operations as $name => $operation) {
            $named[$name] = $operation->withName($name);
        }
        $decorated = new class ($named) implements ResourceMetadataCollectionFactoryInterface {
            public function __construct(private readonly array $operations) {}

            public function create(string $resourceClass): ResourceMetadataCollection
            {
                return new ResourceMetadataCollection($resourceClass, [
                    (new ApiResource())->withOperations(new Operations($this->operations)),
                ]);
            }
        };
        $module = new class implements AdminModuleInterface {
            public function getDefinition(): AdminModule
            {
                return new AdminModule('tag', 'ROLE_CONTRIBUTEUR');
            }
        };

        $factory = new OperationSecurityMetadataFactory($decorated, new AdminModuleRegistry([$module]));
        return iterator_to_array($factory->create('Resource')[0]->getOperations());
    }
}
