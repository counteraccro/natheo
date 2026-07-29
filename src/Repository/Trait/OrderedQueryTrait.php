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
    private const array ALLOWED_ORDER_DIRECTIONS = ['ASC', 'DESC'];

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
        $order = 'DESC';

        if (
            isset($queryParams['orderField']) &&
            $queryParams['orderField'] !== '' &&
            in_array($queryParams['orderField'], $entity::ALLOWED_ORDER_FIELDS, true)
        ) {
            $orderField = $queryParams['orderField'];
        }

        if (
            isset($queryParams['order']) &&
            in_array(strtoupper($queryParams['order']), self::ALLOWED_ORDER_DIRECTIONS, true)
        ) {
            $order = strtoupper($queryParams['order']);
        }

        $orderField = str_contains($orderField, '.') ? $orderField : $entity::DEFAULT_ALIAS . '.' . $orderField;

        return $qb->orderBy($orderField, $order);
    }
}
