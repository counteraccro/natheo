<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider du rendu d'un bloc de page publiée
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Filter\LocaleFilter;
use App\Api\V2\Mapper\Content\FaqMapper;
use App\Api\V2\Output\Content\FaqContent;
use App\Api\V2\Output\Content\ListingContent;
use App\Api\V2\Output\Content\TextContent;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\Resource\Content\PageContent;
use App\Entity\Admin\Content\Page\PageContent as PageContentEntity;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;
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
        private IriConverterInterface $iriConverter,
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
        $locale = $this->apiPageContentService->resolveLocale(...LocaleFilter::getValues($operation));
        $entity = $this->apiPageContentService->findPublishedPageContent((int) $uriVariables['id']);
        $type = PageContentType::tryFrom((int) $entity?->getType());
        $isAvailable = $entity !== null && $this->apiPageContentService->isPageContentAvailable($entity, $locale);
        $content = $type === null || !$isAvailable ? null : $this->getContent($entity, $type, $locale);
        if ($content === null) {
            throw new NotFoundException(
                $this->translator->trans('api_errors.find.page.content.not.found', domain: 'api_errors'),
            );
        }

        $pageContent = new PageContent();
        $pageContent->id = (int) $entity->getId();
        $pageContent->type = strtolower($type->name);
        $pageContent->position = (int) $entity->getRenderBlock();
        $pageContent->content = $content;
        return $pageContent;
    }

    /**
     * Contenu du bloc selon son type, null s'il est vide
     * @param PageContentEntity $entity
     * @param PageContentType $type
     * @param string $locale
     * @return TextContent|FaqContent|ListingContent|null
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getContent(
        PageContentEntity $entity,
        PageContentType $type,
        string $locale,
    ): TextContent|FaqContent|ListingContent|null {
        switch ($type) {
            case PageContentType::TEXT:
                $text = (string) $entity->getPageContentTranslationByLocale($locale)?->getText();
                return $text === '' ? null : new TextContent($this->apiPageContentService->renderMarkdown($text));
            case PageContentType::FAQ:
                $faq = $this->apiPageContentService->findActiveFaq((int) $entity->getTypeId());
                $mapper = new FaqMapper($locale, $this->apiPageContentService->renderMarkdown(...));
                return $faq === null ? null : $mapper->map($faq);
            case PageContentType::LISTING:
                $category = PageCategory::tryFrom((int) $entity->getTypeId());
                if ($category === null) {
                    return null;
                }
                $pages = $this->iriConverter->getIriFromResource(Page::class, operation: new GetCollection());
                return new ListingContent(
                    $category->getSlug(),
                    $this->apiPageContentService->getListingTitle($category, $locale),
                    $pages . '?' . http_build_query(['category' => $category->getSlug(), 'locale' => $locale]),
                );
        }
        return null;
    }
}
