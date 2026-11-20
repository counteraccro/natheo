<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page, recherche, tri et statut demandés sur une collection d'administration
 */

namespace App\Dto\Api\Admin;

use App\Enum\Api\Admin\ActivationStatus;

final readonly class AdminCollectionQuery
{
    /**
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @param string[] $searchFields champs de la ressource dans lesquels chercher
     * @param string|null $orderField champ de la ressource
     * @param \SortDirection $orderDirection
     * @param ActivationStatus|null $status
     */
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public ?string $search = null,
        public array $searchFields = [],
        public ?string $orderField = null,
        public \SortDirection $orderDirection = \SortDirection::Descending,
        public ?ActivationStatus $status = null,
    ) {}
}
