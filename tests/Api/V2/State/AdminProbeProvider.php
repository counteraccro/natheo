<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la ressource de test AdminProbe, filtrée et triée par les filtres d'administration ;
 * la sonde 1 a pour auteur l'utilisateur de la session
 */

namespace App\Tests\Api\V2\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Security\CurrentUserResolver;
use App\Api\V2\State\Provider\Admin\AdminCollectionQueryReader;
use App\Enum\Api\Admin\ActivationStatus;
use App\Tests\Api\V2\Resource\Admin\AdminProbe;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<AdminProbe>
 */
final readonly class AdminProbeProvider implements ProviderInterface
{
    public function __construct(
        private AdminCollectionQueryReader $queryReader,
        private CurrentUserResolver $currentUserResolver,
    ) {}

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
        $user = $this->currentUserResolver->getUser();
        if ($user !== null) {
            $probes[0]->author = new AdminAuthor((int) $user->getId(), (string) $user->getLogin());
        }
        if (!($operation instanceof GetCollection)) {
            return $probes[(int) ($uriVariables['id'] ?? 0) - 1] ?? throw new NotFoundHttpException();
        }

        $query = $this->queryReader->read($operation, $context);
        $probes = array_filter(
            $probes,
            fn(AdminProbe $probe) => ($query->search === null || str_contains($probe->name, $query->search)) &&
                ($query->status === null || $probe->disabled === ($query->status === ActivationStatus::DISABLED)) &&
                ($query->authorId === null || $probe->author?->id === $query->authorId),
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
