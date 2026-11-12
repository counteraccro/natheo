<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Construit la ressource d'un bloc de page à partir de son entité
 */

namespace App\Api\V2\State\Provider\Content;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
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

final readonly class PageContentResourceBuilder
{
    public function __construct(
        private ApiPageContentService $apiPageContentService,
        private IriConverterInterface $iriConverter,
    ) {}

    /**
     * Ressource du bloc, null si son type est inconnu ou son contenu vide dans la langue
     * @param PageContentEntity $entity
     * @param string $locale
     * @param int|null $id identifiant à exposer, celui de l'entité par défaut
     * @return PageContent|null
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function build(PageContentEntity $entity, string $locale, ?int $id = null): ?PageContent
    {
        $type = PageContentType::tryFrom((int) $entity->getType());
        if ($type === null || !$this->apiPageContentService->isPageContentAvailable($entity, $locale)) {
            return null;
        }
        $content = $this->getContent($entity, $type, $locale);
        if ($content === null) {
            return null;
        }

        $pageContent = new PageContent();
        $pageContent->id = $id ?? (int) $entity->getId();
        $pageContent->type = $type->getSlug();
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
                if ($faq === null) {
                    return null;
                }
                $answers = $this->apiPageContentService->renderFaqAnswers($faq, $locale);
                return (new FaqMapper($locale))->map($faq, $answers);
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
