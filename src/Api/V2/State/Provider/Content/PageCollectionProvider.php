<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de la liste paginée des pages publiées
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Parameter\LocaleParameter;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\Mapper\Content\PageMapper;
use App\Service\Api\Content\Page\ApiPageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Page>
 */
final readonly class PageCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ApiPageService $apiPageService,
        private Pagination $pagination,
        private TranslatorInterface $translator,
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
        $locale = $this->apiPageService->resolveLocale(LocaleParameter::getValueFrom($operation));
        $page = $this->pagination->getPage($context);
        $limit = $this->pagination->getLimit($operation, $context);

        $paginator = $this->apiPageService->findPublishedPages(
            $page,
            $limit,
            $this->getQueryValue($operation, 'category'),
            $this->getQueryValue($operation, 'tag'),
        );
        if ($paginator === null) {
            throw new NotFoundHttpException(
                $this->translator->trans('api_errors.find.listing.category.not.found', domain: 'api_errors'),
            );
        }

        $mapper = new PageMapper($locale);
        $pages = [];
        foreach ($paginator as $entity) {
            $pages[] = $mapper->mapSummary($entity);
        }
        return new TraversablePaginator(new \ArrayIterator($pages), $page, $limit, $paginator->count());
    }

    /**
     * @param Operation $operation
     * @param string $key
     * @return string|null
     */
    private function getQueryValue(Operation $operation, string $key): ?string
    {
        $value = $operation->getParameters()?->get($key, QueryParameter::class)?->getValue(null);
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
