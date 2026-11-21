<?php

declare(strict_types=1);
/**
 * Applique une AdminCollectionQuery (recherche, statut, auteur, tri, page) à une requête de collection d'administration
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
     * @param string|null $authorField chemin DQL de l'auteur, null si la ressource n'en a pas
     * @param bool $fetchJoinCollection vrai seulement si la requête joint une collection to-many
     * @param array<string, string> $orderFields tri d'un champ qui diffère de $fields (alias de résultat quand le
     *   chemin est une expression que DQL refuse dans ORDER BY, comme COALESCE)
     * @return Paginator
     */
    protected function paginateAdminCollection(
        QueryBuilder $qb,
        AdminCollectionQuery $query,
        array $fields,
        ?string $disabledField,
        string $defaultOrder,
        ?string $authorField = null,
        bool $fetchJoinCollection = false,
        array $orderFields = [],
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

        if ($query->authorId !== null && $authorField !== null) {
            $qb->andWhere('IDENTITY(' . $authorField . ') = :admin_author')->setParameter(
                'admin_author',
                $query->authorId,
            );
        }

        $qb->orderBy(($orderFields + $fields)[$query->orderField ?? ''] ?? $defaultOrder, $query->orderDirection);

        $qb->setFirstResult(max(0, $query->limit * ($query->page - 1)))->setMaxResults($query->limit);
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection);
        // Sans collection jointe, le comptage n'a pas besoin de reprendre toute la requête en sous-requête
        return $paginator->setUseOutputWalkers($fetchJoinCollection);
    }
}
