<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la collection d'administration des FAQ
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Mapper\Admin\Content\AdminFaqMapper;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Api\V2\Security\AdminPermissionResolver;
use App\Api\V2\State\Provider\Admin\AdminCollectionPaginator;
use App\Api\V2\State\Provider\Admin\AdminCollectionQueryReader;
use App\Service\Api\Admin\Content\ApiAdminFaqService;

/**
 * @implements ProviderInterface<AdminFaq>
 */
final readonly class AdminFaqCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminFaqService $faqService,
        private AdminCollectionQueryReader $queryReader,
        private AdminCollectionPaginator $paginator,
        private AdminPermissionResolver $permissionResolver,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return TraversablePaginator<AdminFaq>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $query = $this->queryReader->read($operation, $context);
        $mapper = new AdminFaqMapper(
            $this->faqService->getLocales(),
            $this->faqService->getContentLocale(),
            $this->faqService->getDefaultLocale(),
        );
        return $this->paginator->paginate(
            $operation,
            $query,
            $this->faqService->getCollection($query),
            fn(array $row) => $mapper->mapSummary(
                $row[0],
                $row['title'],
                (int) $row['categoryCount'],
                (int) $row['questionCount'],
                (int) $row['pageCount'],
                $row['authorId'] === null
                    ? null
                    : new AdminAuthor((int) $row['authorId'], (string) $row['authorLogin']),
                $this->permissionResolver->forContent($row[0]),
            ),
        );
    }
}
