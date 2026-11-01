<?php

declare(strict_types=1);
/**
 * Trait pour gérer l'ordre et le trie des query
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Repository\Trait;

use Doctrine\ORM\QueryBuilder;

trait OrderedQueryTrait
{
    /**
     * Construit le trie de façon sécurisée
     * @param QueryBuilder $qb
     * @param string $entity
     * @param array<string, string> $queryParams
     * @return QueryBuilder
     */
    public function applyOrdering(QueryBuilder $qb, string $entity, array $queryParams): QueryBuilder
    {
        $orderField = 'id';

        if (
            isset($queryParams['orderField']) &&
            $queryParams['orderField'] !== '' &&
            in_array($queryParams['orderField'], $entity::ALLOWED_ORDER_FIELDS, true)
        ) {
            $orderField = $queryParams['orderField'];
        }

        $orderField = str_contains($orderField, '.') ? $orderField : $entity::DEFAULT_ALIAS . '.' . $orderField;

        return $qb->orderBy($orderField, $this->toSortDirection($queryParams['order'] ?? null));
    }

    /**
     * Convertit une direction de tri reçue en chaîne ('asc' / 'desc', casse indifférente) en SortDirection
     * Toute autre valeur renvoie $default
     * @param string|null $order
     * @param \SortDirection $default
     * @return \SortDirection
     */
    public function toSortDirection(
        ?string $order,
        \SortDirection $default = \SortDirection::Descending,
    ): \SortDirection {
        return match (strtoupper((string) $order)) {
            'ASC' => \SortDirection::Ascending,
            'DESC' => \SortDirection::Descending,
            default => $default,
        };
    }
}
