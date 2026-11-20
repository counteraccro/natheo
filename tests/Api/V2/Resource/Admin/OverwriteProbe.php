<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource d'administration chargée uniquement en env test, dont la lecture et la modification sont surchargées par
 * tests/Api/V2/Overwrite/overwrite.yaml
 */

namespace App\Tests\Api\V2\Resource\Admin;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Tests\Api\V2\Module\ProbeModule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'OverwriteProbe',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => ProbeModule::ID],
        normalizationContext: ['groups' => ['overwrite_probe:read']],
        denormalizationContext: ['groups' => ['overwrite_probe:write']],
        operations: [
            new GetCollection(
                name: 'admin_overwrite_probe_get_collection',
                uriTemplate: '/admin/overwrite_probes',
                provider: [self::class, 'provide'],
            ),
            new Get(
                name: 'admin_overwrite_probe_get',
                uriTemplate: '/admin/overwrite_probes/{id}',
                provider: [self::class, 'provide'],
            ),
            new Post(
                name: 'admin_overwrite_probe_post',
                uriTemplate: '/admin/overwrite_probes',
                processor: [self::class, 'process'],
            ),
            new Patch(
                name: 'admin_overwrite_probe_patch',
                uriTemplate: '/admin/overwrite_probes/{id}',
                security: "is_granted('ROLE_ADMIN')",
                provider: [self::class, 'provide'],
                processor: [self::class, 'process'],
            ),
        ],
    ),
]
final class OverwriteProbe
{
    /**
     * Identifiant de la seule sonde existante
     * @var int
     */
    public const int EXISTING_ID = 1;

    /**
     * Valeur de source donnée par le provider ou le processor d'origine
     * @var string
     */
    public const string ORIGINAL = 'original';

    /**
     * Valeur de source donnée par une classe de surcharge
     * @var string
     */
    public const string OVERWRITE = 'overwrite';

    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['overwrite_probe:read'])]
    public ?int $id = null;

    #[ApiProperty(required: true)]
    #[Groups(['overwrite_probe:read', 'overwrite_probe:write'])]
    public string $name = '';

    #[ApiProperty(required: true)]
    #[Groups(['overwrite_probe:read'])]
    public string $source = self::ORIGINAL;

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return OverwriteProbe|array<OverwriteProbe>
     */
    public static function provide(Operation $operation, array $uriVariables = [], array $context = []): self|array
    {
        $probe = new self();
        $probe->id = self::EXISTING_ID;
        $probe->name = 'sonde';

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
     * @return OverwriteProbe
     */
    public static function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): self {
        $data->id ??= 2;
        $data->source = self::ORIGINAL;
        return $data;
    }
}
