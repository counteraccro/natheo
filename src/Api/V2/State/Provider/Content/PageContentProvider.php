<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider du rendu d'un bloc de page publiée
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Resource\Content\PageContent;
use App\Service\Api\Content\Page\ApiPageContentService;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<PageContent>
 */
final readonly class PageContentProvider implements ProviderInterface
{
    public function __construct(
        private ApiPageContentService $apiPageContentService,
        private PageContentResourceBuilder $pageContentResourceBuilder,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return PageContent
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PageContent
    {
        $locale = $this->apiPageContentService->getContentLocale();
        $entity = $this->apiPageContentService->findPublishedPageContent((int) $uriVariables['id']);
        $pageContent = $entity === null ? null : $this->pageContentResourceBuilder->build($entity, $locale);
        if ($pageContent === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.page.content.not.found', domain: 'api_errors'),
            );
        }
        return $pageContent;
    }
}
