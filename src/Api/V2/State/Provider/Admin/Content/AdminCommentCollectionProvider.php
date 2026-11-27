<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Liste des commentaires de l'API d'administration
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Filter\Admin\CommentStatusFilter;
use App\Api\V2\Filter\Admin\PageIdFilter;
use App\Api\V2\Mapper\Admin\Content\AdminCommentMapper;
use App\Api\V2\Output\Admin\Content\AdminCommentPage;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Resource\Admin\Content\AdminComment;
use App\Api\V2\Security\AdminPermissionResolver;
use App\Api\V2\State\Provider\Admin\AdminCollectionPaginator;
use App\Api\V2\State\Provider\Admin\AdminCollectionQueryReader;
use App\Service\Api\Admin\Content\ApiAdminCommentService;

final readonly class AdminCommentCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminCommentService $commentService,
        private AdminCollectionQueryReader $queryReader,
        private AdminCollectionPaginator $paginator,
        private AdminPermissionResolver $permissionResolver,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return TraversablePaginator<AdminComment>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $query = $this->queryReader->read($operation, $context);
        $mapper = new AdminCommentMapper();
        $permissions = $this->permissionResolver->forUnowned();
        return $this->paginator->paginate(
            $operation,
            $query,
            $this->commentService->getCollection(
                $query,
                CommentStatusFilter::getValue($operation),
                PageIdFilter::getValue($operation),
            ),
            fn(array $row) => $mapper->mapSummary(
                $row[0],
                $this->commentService->getExcerpt((string) $row[0]->getComment()),
                new AdminCommentPage((int) $row['pageId'], (string) $row['pageTitle']),
                $row['moderatorId'] === null
                    ? null
                    : new AdminAuthor((int) $row['moderatorId'], (string) $row['moderatorLogin']),
                $permissions,
            ),
        );
    }
}
