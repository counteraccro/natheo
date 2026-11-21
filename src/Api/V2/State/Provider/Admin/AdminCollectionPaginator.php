<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lit une page de collection d'administration et garde sa requête SQL quand le paramètre « sql » la demande
 */

namespace App\Api\V2\State\Provider\Admin;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use App\Api\V2\Metadata\SqlParameter;
use App\Dto\Api\Admin\AdminCollectionQuery;
use App\Service\Admin\Tools\SqlManager\SqlQueryInliner;
use App\Service\Admin\Tools\SqlManager\SqlRecorder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class AdminCollectionPaginator
{
    /**
     * Attribut de la requête qui porte la requête SQL de la page, ajoutée à la réponse par AdminSqlCollectionNormalizer
     * @var string
     */
    public const string ATTRIBUTE = '_admin_collection_sql';

    public function __construct(
        private SqlRecorder $recorder,
        private SqlQueryInliner $inliner,
        private RequestStack $requestStack,
    ) {}

    /**
     * Éléments de la page convertis par $map, avec le nombre total d'éléments
     * @template E
     * @template R of object
     * @param Operation $operation
     * @param AdminCollectionQuery $query
     * @param Paginator<E> $paginator
     * @param callable(E): R $map
     * @return TraversablePaginator<R>
     */
    public function paginate(
        Operation $operation,
        AdminCollectionQuery $query,
        Paginator $paginator,
        callable $map,
    ): TraversablePaginator {
        $load = fn(): array => iterator_to_array($paginator->getIterator(), false);

        if (SqlParameter::isRequested($operation)) {
            [$entities, $queries] = $this->recorder->record($load);
            $last = end($queries);
            if ($last !== false) {
                $this->requestStack
                    ->getCurrentRequest()
                    ?->attributes->set(self::ATTRIBUTE, $this->inliner->inline($last));
            }
        } else {
            $entities = $load();
        }

        return new TraversablePaginator(
            new \ArrayIterator(array_map($map, $entities)),
            $query->page,
            $query->limit,
            count($paginator),
        );
    }
}
