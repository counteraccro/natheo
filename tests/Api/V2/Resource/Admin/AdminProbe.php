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
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\ConflictException;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Filter\Admin\AdminOrderFilter;
use App\Api\V2\Filter\Admin\AuthorFilter;
use App\Api\V2\Filter\Admin\SearchFilter;
use App\Api\V2\Filter\Admin\StatusFilter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Output\Admin\Global\AdminLabelTranslation;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Tests\Api\V2\Module\ProbeModule;
use App\Tests\Api\V2\State\AdminProbeProcessor;
use App\Tests\Api\V2\State\AdminProbeProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminProbe',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => ProbeModule::ID],
        normalizationContext: [
            'groups' => ['admin_probe:read', Permissions::GROUP, AdminAuthor::GROUP, AdminLabelTranslation::GROUP],
        ],
        denormalizationContext: ['groups' => ['admin_probe:write']],
        operations: [
            new GetCollection(
                name: 'admin_probe_get_collection',
                uriTemplate: '/admin/probes',
                provider: AdminProbeProvider::class,
                parameters: [
                    SearchFilter::PARAMETER => new QueryParameter(filter: SearchFilter::class, properties: ['name']),
                    AdminOrderFilter::PARAMETER => new QueryParameter(
                        filter: AdminOrderFilter::class,
                        properties: ['id', 'name'],
                    ),
                    StatusFilter::PARAMETER => new QueryParameter(filter: StatusFilter::class, property: 'disabled'),
                    AuthorFilter::PARAMETER => new QueryParameter(filter: AuthorFilter::class, property: 'author'),
                ],
            ),
            new Get(name: 'admin_probe_get', uriTemplate: '/admin/probes/{id}', provider: AdminProbeProvider::class),
            new Post(
                name: 'admin_probe_post',
                uriTemplate: '/admin/probes',
                extraProperties: [
                    OperationSecurityMetadataFactory::MODULE => ProbeModule::ID,
                    OperationSecurityMetadataFactory::FRONT_ALLOWED => true,
                ],
                processor: AdminProbeProcessor::class,
            ),
            new Patch(
                name: 'admin_probe_patch',
                uriTemplate: '/admin/probes/{id}',
                errors: [PreconditionFailedException::class],
                provider: AdminProbeProvider::class,
                processor: AdminProbeProcessor::class,
            ),
            new Delete(
                name: 'admin_probe_delete',
                uriTemplate: '/admin/probes/{id}',
                errors: [PreconditionFailedException::class, ConflictException::class],
                security: "is_granted('ROLE_ADMIN')",
                provider: AdminProbeProvider::class,
                processor: AdminProbeProcessor::class,
            ),
        ],
    ),
]
final class AdminProbe implements VersionedResourceInterface
{
    /**
     * Identifiant de la sonde lue par les opérations d'item
     * @var int
     */
    public const int EXISTING_ID = 1;

    /**
     * Identifiant de la sonde dont la suppression est refusée en 409
     * @var int
     */
    public const int USED_ID = 3;

    /**
     * Version des sondes lues
     * @var string
     */
    public const string UPDATED_AT = '2026-01-01T10:00:00+00:00';

    /**
     * Version d'une sonde après une écriture
     * @var string
     */
    public const string UPDATED_AT_AFTER_WRITE = '2026-01-02T10:00:00+00:00';

    #[ApiProperty(required: true, identifier: true)]
    #[Groups(['admin_probe:read'])]
    public ?int $id = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_probe:read', 'admin_probe:write'])]
    public string $name = '';

    #[ApiProperty(required: true)]
    #[Groups(['admin_probe:read'])]
    public bool $disabled = false;

    /**
     * @var AdminLabelTranslation[]
     */
    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_probe:read'])]
    public array $translations = [];

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_probe:read'])]
    public ?AdminAuthor $author = null;

    #[ApiProperty(required: true)]
    #[Groups(['admin_probe:read'])]
    public ?string $updatedAt = self::UPDATED_AT;

    #[ApiProperty(required: true, genId: false)]
    #[Groups(['admin_probe:read'])]
    public ?Permissions $permissions = null;

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Sondes 1 « alpha », 2 « beta » (désactivée) et 3 « gamma »
     * @return self[]
     */
    public static function all(): array
    {
        return [self::create(1, 'alpha', false), self::create(2, 'beta', true), self::create(3, 'gamma', false)];
    }

    /**
     * @param int $id
     * @param string $name
     * @param bool $disabled
     * @return self
     */
    public static function create(int $id, string $name, bool $disabled): self
    {
        $probe = new self();
        $probe->id = $id;
        $probe->name = $name;
        $probe->disabled = $disabled;
        $probe->translations = [new AdminLabelTranslation('fr', $name), new AdminLabelTranslation('en', $name)];
        $probe->permissions = new Permissions(edit: true, disable: true, delete: false);
        return $probe;
    }
}
