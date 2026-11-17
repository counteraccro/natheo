<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource d'administration chargée uniquement en env test pour tester les conventions de l'API d'administration
 */

namespace App\Tests\Api\V2\Resource\Admin;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Tests\Api\V2\Module\ProbeModule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[
    ApiResource(
        shortName: 'AdminProbe',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => ProbeModule::ID],
        operations: [
            new GetCollection(
                name: 'admin_probe_get_collection',
                uriTemplate: '/admin/probes',
                provider: [self::class, 'provide'],
            ),
            new Get(name: 'admin_probe_get', uriTemplate: '/admin/probes/{id}', provider: [self::class, 'provide']),
            new Post(
                name: 'admin_probe_post',
                uriTemplate: '/admin/probes',
                extraProperties: [
                    OperationSecurityMetadataFactory::MODULE => ProbeModule::ID,
                    OperationSecurityMetadataFactory::FRONT_ALLOWED => true,
                ],
                processor: [self::class, 'process'],
            ),
            new Patch(
                name: 'admin_probe_patch',
                uriTemplate: '/admin/probes/{id}',
                provider: [self::class, 'provide'],
                processor: [self::class, 'process'],
            ),
            new Delete(
                name: 'admin_probe_delete',
                uriTemplate: '/admin/probes/{id}',
                security: "is_granted('ROLE_ADMIN')",
                provider: [self::class, 'provide'],
                processor: [self::class, 'process'],
            ),
        ],
    ),
]
final class AdminProbe
{
    /**
     * Identifiant de la seule sonde existante
     * @var int
     */
    public const int EXISTING_ID = 1;

    #[ApiProperty(required: true, identifier: true)]
    public ?int $id = null;

    #[ApiProperty(required: true)]
    public string $name = '';

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminProbe|array<AdminProbe>
     */
    public static function provide(Operation $operation, array $uriVariables = [], array $context = []): self|array
    {
        $probe = new self();
        $probe->id = self::EXISTING_ID;
        $probe->name = 'probe';

        if ($operation instanceof GetCollection) {
            return [$probe];
        }
        if ((int) ($uriVariables['id'] ?? 0) !== self::EXISTING_ID) {
            throw new NotFoundHttpException();
        }
        return $probe;
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminProbe|null
     */
    public static function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): ?self {
        if ($operation instanceof Delete) {
            return null;
        }
        $data->id ??= 2;
        return $data;
    }
}
