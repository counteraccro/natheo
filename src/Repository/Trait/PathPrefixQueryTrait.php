<?php

declare(strict_types=1);
/**
 * Trait pour filtrer une entité par un champ "path" exact ou par segment de chemin complet
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Repository\Trait;

use Doctrine\ORM\QueryBuilder;

trait PathPrefixQueryTrait
{
    /**
     * Filtre sur un champ "path" dont la valeur est exactement $pathPrefix ou commence par
     * $pathPrefix suivi d'un séparateur de répertoire (segment de chemin complet, jamais une
     * simple sous-chaîne : "/Doc" ne matche pas "/Docker")
     * @param QueryBuilder $qb
     * @param string $alias
     * @param string $pathPrefix
     * @return QueryBuilder
     */
    public function applyPathPrefixFilter(QueryBuilder $qb, string $alias, string $pathPrefix): QueryBuilder
    {
        $escaped = addcslashes($pathPrefix, '\\%_');

        return $qb
            ->andWhere($alias . '.path = :exact OR ' . $alias . '.path LIKE :prefix')
            ->setParameter('exact', $pathPrefix)
            ->setParameter('prefix', $escaped . '/%');
    }
}
