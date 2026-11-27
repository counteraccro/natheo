<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lit la page, la recherche, le tri, le statut et l'auteur demandés sur une collection d'administration
 */

namespace App\Api\V2\State\Provider\Admin;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use App\Api\V2\Filter\Admin\AdminOrderFilter;
use App\Api\V2\Filter\Admin\AuthorFilter;
use App\Api\V2\Filter\Admin\SearchFilter;
use App\Api\V2\Filter\Admin\StatusFilter;
use App\Api\V2\Security\CurrentUserResolver;
use App\Dto\Api\Admin\AdminCollectionQuery;

final readonly class AdminCollectionQueryReader
{
    public function __construct(private Pagination $pagination, private CurrentUserResolver $currentUserResolver) {}

    /**
     * Lit les paramètres des filtres d'administration et la pagination de l'opération
     * @param Operation $operation
     * @param array $context
     * @return AdminCollectionQuery
     */
    public function read(Operation $operation, array $context): AdminCollectionQuery
    {
        $order = AdminOrderFilter::getValue($operation);
        return new AdminCollectionQuery(
            page: $this->pagination->getPage($context),
            limit: $this->pagination->getLimit($operation, $context),
            search: SearchFilter::getValue($operation),
            searchFields: SearchFilter::getFields($operation),
            orderField: $order['field'] ?? null,
            orderDirection: $order['direction'] ?? \SortDirection::Descending,
            status: StatusFilter::getValue($operation),
            authorId: AuthorFilter::isMine($operation) ? $this->currentUserResolver->getUser()?->getId() ?? 0 : null,
        );
    }
}
