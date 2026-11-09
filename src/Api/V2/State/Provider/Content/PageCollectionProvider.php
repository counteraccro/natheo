<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la liste paginée des pages publiées, filtrée par catégorie, tag, slug ou page d'accueil
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Filter\LocaleFilter;
use App\Api\V2\Filter\PageCategoryFilter;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\Mapper\Content\PageMapper;
use App\Service\Api\Content\Page\ApiPageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @implements ProviderInterface<Page>
 */
final readonly class PageCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ApiPageService $apiPageService,
        private Pagination $pagination,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return TraversablePaginator<Page>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $locale = $this->apiPageService->resolveLocale(...LocaleFilter::getValues($operation));
        $page = $this->pagination->getPage($context);
        $limit = $this->pagination->getLimit($operation, $context);

        $parameters = $operation->getParameters();
        $tag = $parameters?->get('tag', QueryParameter::class)?->getValue(null);
        $slug = $parameters?->get('slug', QueryParameter::class)?->getValue(null);
        $landing = $parameters?->get('landing', QueryParameter::class)?->getValue(null);
        $paginator = $this->apiPageService->findPublishedPages(
            $page,
            $limit,
            $locale,
            PageCategoryFilter::getValue($operation),
            is_numeric($tag) ? (int) $tag : null,
            is_string($slug) ? $slug : null,
            is_bool($landing) ? $landing : null,
        );

        $mapper = new PageMapper($locale);
        $pages = [];
        foreach ($paginator as $entity) {
            $pages[] = $mapper->mapSummary($entity);
        }
        return new TraversablePaginator(new \ArrayIterator($pages), $page, $limit, $paginator->count());
    }
}
