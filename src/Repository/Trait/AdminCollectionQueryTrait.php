<?php

declare(strict_types=1);
/**
 * Applique une AdminCollectionQuery (recherche, statut, tri, page) à une requête de collection d'administration
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Repository\Trait;

use App\Enum\Api\Admin\ActivationStatus;
use App\Dto\Api\Admin\AdminCollectionQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait AdminCollectionQueryTrait
{
    /**
     * Un champ absent de $fields est ignoré par la recherche et le tri
     * @param QueryBuilder $qb
     * @param AdminCollectionQuery $query
     * @param array<string, string> $fields champ de la ressource => chemin DQL (ex : 'name' => 't.name')
     * @param string|null $disabledField chemin DQL du booléen « désactivé », null si la ressource n'en a pas
     * @param string $defaultOrder chemin DQL du tri par défaut
     * @return Paginator
     */
    protected function paginateAdminCollection(
        QueryBuilder $qb,
        AdminCollectionQuery $query,
        array $fields,
        ?string $disabledField,
        string $defaultOrder,
    ): Paginator {
        $searchPaths = array_values(array_intersect_key($fields, array_flip($query->searchFields)));
        if ($query->search !== null && $searchPaths !== []) {
            $conditions = array_map(fn(string $path) => 'LOWER(' . $path . ') LIKE :admin_search', $searchPaths);
            $qb->andWhere($qb->expr()->orX(...$conditions))->setParameter(
                'admin_search',
                '%' . addcslashes(mb_strtolower($query->search), '%_\\') . '%',
            );
        }

        if ($query->status !== null && $disabledField !== null) {
            $qb->andWhere($disabledField . ' = :admin_disabled')->setParameter(
                'admin_disabled',
                $query->status === ActivationStatus::DISABLED,
            );
        }

        $qb->orderBy($fields[$query->orderField ?? ''] ?? $defaultOrder, $query->orderDirection);

        $qb->setFirstResult(max(0, $query->limit * ($query->page - 1)))->setMaxResults($query->limit);
        return new Paginator($qb->getQuery(), true);
    }
}
