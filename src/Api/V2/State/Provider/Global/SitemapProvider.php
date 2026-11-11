<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la liste paginée des pages du sitemap
 */

namespace App\Api\V2\State\Provider\Global;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Mapper\Global\SitemapEntryMapper;
use App\Api\V2\Resource\Global\SitemapEntry;
use App\Service\Api\Global\ApiSitemapService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @implements ProviderInterface<SitemapEntry>
 */
final readonly class SitemapProvider implements ProviderInterface
{
    public function __construct(private ApiSitemapService $apiSitemapService, private Pagination $pagination) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return TraversablePaginator<SitemapEntry>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $page = $this->pagination->getPage($context);
        $limit = $this->pagination->getLimit($operation, $context);
        $paginator = $this->apiSitemapService->findSitemapPages($page, $limit);

        $mapper = new SitemapEntryMapper();
        $entries = [];
        foreach ($paginator as $entity) {
            $entries[] = $mapper->mapSummary($entity, $this->apiSitemapService->getPaths($entity));
        }
        return new TraversablePaginator(new \ArrayIterator($entries), $page, $limit, $paginator->count());
    }
}
