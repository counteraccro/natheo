<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit un bloc de page et son contenu calculé en ressource PageContent de l'API v2
 */

namespace App\Api\V2\Mapper\Content;

use App\Api\V2\Output\Content\FaqContent;
use App\Api\V2\Output\Content\ListingContent;
use App\Api\V2\Output\Content\TextContent;
use App\Api\V2\Resource\Content\PageContent;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Page\PageContent as PageContentEntity;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;

final readonly class PageContentMapper
{
    /**
     * @param string $locale
     */
    public function __construct(private string $locale) {}

    /**
     * @param PageContentEntity $entity
     * @param array{type: PageContentType, html?: string, faq?: Faq, answers?: array<int, string>, category?: PageCategory, title?: string} $rendered
     *        contenu calculé par ApiPageContentService::getRenderedContent()
     * @param string $pagesIri IRI de la collection des pages, base de l'URL d'un listing
     * @param int|null $id identifiant à exposer, celui de l'entité par défaut
     * @return PageContent
     */
    public function map(PageContentEntity $entity, array $rendered, string $pagesIri, ?int $id = null): PageContent
    {
        $pageContent = new PageContent();
        $pageContent->id = $id ?? (int) $entity->getId();
        $pageContent->type = $rendered['type']->getSlug();
        $pageContent->position = (int) $entity->getRenderBlock();
        $pageContent->content = $this->mapContent($rendered, $pagesIri);
        return $pageContent;
    }

    /**
     * @param array $rendered
     * @param string $pagesIri
     * @return TextContent|FaqContent|ListingContent
     */
    private function mapContent(array $rendered, string $pagesIri): TextContent|FaqContent|ListingContent
    {
        return match ($rendered['type']) {
            PageContentType::TEXT => new TextContent($rendered['html']),
            PageContentType::FAQ => (new FaqMapper($this->locale))->map($rendered['faq'], $rendered['answers']),
            PageContentType::LISTING => $this->mapListing($rendered['category'], $rendered['title'], $pagesIri),
        };
    }

    /**
     * @param PageCategory $category
     * @param string $title
     * @param string $pagesIri
     * @return ListingContent
     */
    private function mapListing(PageCategory $category, string $title, string $pagesIri): ListingContent
    {
        $query = http_build_query(['category' => $category->getSlug(), 'locale' => $this->locale]);
        return new ListingContent($category->getSlug(), $title, $pagesIri . '?' . $query);
    }
}
