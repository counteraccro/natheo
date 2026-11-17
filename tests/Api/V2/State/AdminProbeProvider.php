<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la ressource de test AdminProbe, filtrée et triée par les filtres d'administration
 */

namespace App\Tests\Api\V2\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\State\Provider\Admin\AdminCollectionQueryReader;
use App\Enum\Api\Admin\ActivationStatus;
use App\Tests\Api\V2\Resource\Admin\AdminProbe;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<AdminProbe>
 */
final readonly class AdminProbeProvider implements ProviderInterface
{
    public function __construct(private AdminCollectionQueryReader $queryReader) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminProbe|TraversablePaginator<AdminProbe>
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): AdminProbe|TraversablePaginator {
        $probes = AdminProbe::all();
        if (!($operation instanceof GetCollection)) {
            return $probes[(int) ($uriVariables['id'] ?? 0) - 1] ?? throw new NotFoundHttpException();
        }

        $query = $this->queryReader->read($operation, $context);
        $probes = array_filter(
            $probes,
            fn(AdminProbe $probe) => ($query->search === null || str_contains($probe->name, $query->search)) &&
                ($query->status === null || $probe->disabled === ($query->status === ActivationStatus::DISABLED)),
        );

        $field = $query->orderField ?? 'id';
        $direction = $query->orderField === null ? \SortDirection::Ascending : $query->orderDirection;
        usort($probes, fn(AdminProbe $a, AdminProbe $b) => $a->{$field} <=> $b->{$field});
        $probes = $direction === \SortDirection::Descending ? array_reverse($probes) : $probes;

        return new TraversablePaginator(
            new \ArrayIterator(array_slice($probes, ($query->page - 1) * $query->limit, $query->limit)),
            $query->page,
            $query->limit,
            count($probes),
        );
    }
}
