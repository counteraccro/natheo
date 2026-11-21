<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la collection d'administration des tags
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Mapper\Admin\Content\AdminTagMapper;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Resource\Admin\Content\AdminTag;
use App\Api\V2\Security\AdminPermissionResolver;
use App\Api\V2\State\Provider\Admin\AdminCollectionPaginator;
use App\Api\V2\State\Provider\Admin\AdminCollectionQueryReader;
use App\Service\Api\Admin\Content\ApiAdminTagService;

/**
 * @implements ProviderInterface<AdminTag>
 */
final readonly class AdminTagCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminTagService $tagService,
        private AdminCollectionQueryReader $queryReader,
        private AdminCollectionPaginator $paginator,
        private AdminPermissionResolver $permissionResolver,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return TraversablePaginator<AdminTag>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $query = $this->queryReader->read($operation, $context);
        $mapper = new AdminTagMapper(
            $this->tagService->getLocales(),
            $this->tagService->getContentLocale(),
            $this->tagService->getDefaultLocale(),
        );
        return $this->paginator->paginate(
            $operation,
            $query,
            $this->tagService->getCollection($query),
            fn(array $row) => $mapper->mapSummary(
                $row[0],
                $row['label'],
                (int) $row['pageCount'],
                $row['authorId'] === null ? null : new AdminAuthor((int) $row['authorId'], (string) $row['authorLogin']),
                $this->permissionResolver->forContent($row[0]),
            ),
        );
    }
}
