<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource PageContent de l'API v2
 */

namespace App\Tests\Api\V2\Content;

use App\Api\V2\Resource\Content\PageContent as PageContentResource;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Tests\Api\V2\AppApiV2TestCase;

class PageContentTest extends AppApiV2TestCase
{
    /**
     * Rendu des blocs texte (HTML), FAQ (structure) et listing (catégorie + URL de la liste)
     * @return void
     */
    public function testRenderedContent(): void
    {
        $page = $this->createPageAllDataDefault();
        $listing = $this->createPageContent($page, [
            'type' => PageContentType::LISTING->value,
            'typeId' => PageCategory::EVENEMENT->value,
        ]);

        foreach ($page->getPageContents() as $pageContent) {
            $content = $this->decode($this->requestV2('GET', '/page_contents/' . $pageContent->getId() . '?locale=fr'));
            $this->assertMatchesResourceItemJsonSchema(PageContentResource::class);
            $this->assertEquals('/api/v2/page_contents/' . $pageContent->getId(), $content['@id']);
            $this->assertEquals($pageContent->getRenderBlock(), $content['position']);

            match ($pageContent->getType()) {
                PageContentType::TEXT->value => $this->assertStringContainsString('<p>', $content['content']['html']),
                PageContentType::FAQ->value => $this->assertEquals(
                    ['@type', 'title', 'categories'],
                    array_keys($content['content']),
                ),
                default => null,
            };
        }

        $content = $this->decode($this->requestV2('GET', '/page_contents/' . $listing->getId() . '?locale=fr'));
        $this->assertMatchesResourceItemJsonSchema(PageContentResource::class);
        $this->assertEquals('listing', $content['type']);
        $this->assertEquals('evenement', $content['content']['category']);
        $this->assertEquals(
            $this->translator->trans(
                'page.content.listing.title',
                ['category' => $this->translator->trans('page.category.event', domain: 'page')],
                domain: 'page',
                locale: 'fr',
            ),
            $content['content']['title'],
        );
        $this->assertEquals('/api/v2/pages?category=evenement&locale=fr', $content['content']['pages']);
    }

    /**
     * Bloc sans contenu dans la langue demandée ou FAQ désactivée : absent de la page et 404
     * @return void
     */
    public function testUnavailableContent(): void
    {
        $page = $this->createPage(customData: ['status' => PageStatus::PUBLISH->value, 'disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }
        $text = $this->createPageContent($page);
        $this->createPageContentTranslation($text, ['locale' => 'fr', 'text' => 'Texte']);
        $this->createPageContentTranslation($text, ['locale' => 'en', 'text' => '']);
        $faq = $this->createPageContent($page, [
            'type' => PageContentType::FAQ->value,
            'typeId' => $this->createFaq(customData: ['disabled' => true])->getId(),
        ]);

        $ids = fn(string $locale) => array_column(
            $this->decode($this->requestV2('GET', '/pages/' . $page->getId() . '?locale=' . $locale))['contents'],
            'id',
        );
        $this->assertEquals([$text->getId()], $ids('fr'));
        $this->assertEquals([], $ids('en'));

        $this->assertEquals(200, $this->requestV2('GET', '/page_contents/' . $text->getId() . '?locale=fr')->getStatusCode());
        $this->assertProblem($this->requestV2('GET', '/page_contents/' . $text->getId() . '?locale=en'), 404);
        $this->assertProblem($this->requestV2('GET', '/page_contents/' . $faq->getId() . '?locale=fr'), 404);
    }

    /**
     * Bloc d'une page au statut brouillon ou inexistant : 404
     * @return void
     */
    public function testNotVisible(): void
    {
        $draft = $this->createPageAllDataDefault();
        $draft->setStatus(PageStatus::DRAFT->value);
        $this->persistAndFlush($draft);

        $this->assertProblem($this->requestV2('GET', '/page_contents/' . $draft->getPageContents()->first()->getId()), 404);
        $this->assertProblem($this->requestV2('GET', '/page_contents/999999999'), 404);
    }
}
