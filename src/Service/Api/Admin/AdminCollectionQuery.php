<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Page, recherche, tri et statut demandés sur une collection d'administration
 */

namespace App\Service\Api\Admin;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use App\Api\V2\Filter\Admin\OrderFilter;
use App\Api\V2\Filter\Admin\SearchFilter;
use App\Api\V2\Filter\Admin\StatusFilter;
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

    /**
     * Lit les paramètres des filtres d'administration et la pagination de l'opération
     * @param Operation $operation
     * @param Pagination $pagination
     * @param array $context
     * @return self
     */
    public static function fromOperation(Operation $operation, Pagination $pagination, array $context): self
    {
        $order = OrderFilter::getValue($operation);
        return new self(
            page: $pagination->getPage($context),
            limit: $pagination->getLimit($operation, $context),
            search: SearchFilter::getValue($operation),
            searchFields: SearchFilter::getFields($operation),
            orderField: $order['field'] ?? null,
            orderDirection: $order['direction'] ?? \SortDirection::Descending,
            status: StatusFilter::getValue($operation),
        );
    }
}
