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
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\State\Pagination\TraversablePaginator;
use App\Api\V2\Concurrency\VersionedResourceInterface;
use App\Api\V2\Exception\PreconditionFailedException;
use App\Api\V2\Filter\Admin\OrderFilter;
use App\Api\V2\Filter\Admin\SearchFilter;
use App\Api\V2\Filter\Admin\StatusFilter;
use App\Api\V2\Metadata\OperationSecurityMetadataFactory;
use App\Api\V2\Output\Admin\Permissions;
use App\Enum\Api\Admin\ActivationStatus;
use App\Tests\Api\V2\Module\ProbeModule;
use App\Tests\Api\V2\State\AdminProbeProcessor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'AdminProbe',
        extraProperties: [OperationSecurityMetadataFactory::MODULE => ProbeModule::ID],
        normalizationContext: ['groups' => ['admin_probe:read', Permissions::GROUP]],
        denormalizationContext: ['groups' => ['admin_probe:write']],
        operations: [
            new GetCollection(
                name: 'admin_probe_get_collection',
                uriTemplate: '/admin/probes',
                provider: [self::class, 'provide'],
                parameters: [
                    SearchFilter::PARAMETER => new QueryParameter(filter: SearchFilter::class, properties: ['name']),
                    OrderFilter::PARAMETER => new QueryParameter(
                        filter: OrderFilter::class,
                        properties: ['id', 'name'],
                    ),
                    StatusFilter::PARAMETER => new QueryParameter(filter: StatusFilter::class, property: 'disabled'),
                ],
            ),
            new Get(name: 'admin_probe_get', uriTemplate: '/admin/probes/{id}', provider: [self::class, 'provide']),
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
                provider: [self::class, 'provide'],
                processor: AdminProbeProcessor::class,
            ),
            new Delete(
                name: 'admin_probe_delete',
                uriTemplate: '/admin/probes/{id}',
                errors: [PreconditionFailedException::class],
                security: "is_granted('ROLE_ADMIN')",
                provider: [self::class, 'provide'],
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
     * Sondes 1 « alpha », 2 « beta » (désactivée) et 3 « gamma », filtrées et triées par les filtres d'administration
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminProbe|TraversablePaginator<AdminProbe>
     */
    public static function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): self|TraversablePaginator {
        $probes = [self::create(1, 'alpha', false), self::create(2, 'beta', true), self::create(3, 'gamma', false)];

        if (!($operation instanceof GetCollection)) {
            return $probes[(int) ($uriVariables['id'] ?? 0) - 1] ?? throw new NotFoundHttpException();
        }

        $search = SearchFilter::getValue($operation);
        $status = StatusFilter::getValue($operation);
        $probes = array_filter(
            $probes,
            fn(self $probe) => ($search === null || str_contains($probe->name, $search)) &&
                ($status === null || $probe->disabled === ($status === ActivationStatus::DISABLED)),
        );

        $order = OrderFilter::getValue($operation) ?? ['field' => 'id', 'direction' => \SortDirection::Ascending];
        usort($probes, fn(self $a, self $b) => $a->{$order['field']} <=> $b->{$order['field']});
        $probes = $order['direction'] === \SortDirection::Descending ? array_reverse($probes) : $probes;

        $limit = max(1, (int) ($context['filters']['itemsPerPage'] ?? 20));
        $page = max(1, (int) ($context['filters']['page'] ?? 1));
        return new TraversablePaginator(
            new \ArrayIterator(array_slice($probes, ($page - 1) * $limit, $limit)),
            $page,
            $limit,
            count($probes),
        );
    }

    /**
     * @param int $id
     * @param string $name
     * @param bool $disabled
     * @return self
     */
    private static function create(int $id, string $name, bool $disabled): self
    {
        $probe = new self();
        $probe->id = $id;
        $probe->name = $name;
        $probe->disabled = $disabled;
        $probe->permissions = new Permissions(edit: true, disable: true, delete: false);
        return $probe;
    }
}
