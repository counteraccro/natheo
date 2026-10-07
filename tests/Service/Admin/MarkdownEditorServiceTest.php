<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test MarkdownEditorService
 */

namespace App\Tests\Service\Admin;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Admin\MarkdownEditorService;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MarkdownEditorServiceTest extends AppWebTestCase
{
    /**
     * @var MarkdownEditorService
     */
    private MarkdownEditorService $markdownEditorService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->markdownEditorService = $this->container->get(MarkdownEditorService::class);
    }

    /**
     * Crée une page publiée et active, traduite dans les locales demandées
     * @param array $locales
     * @param array $customData
     * @return Page
     */
    private function createLinkablePage(array $locales, array $customData = []): Page
    {
        $page = $this->createPage(
            customData: array_merge(
                [
                    'status' => PageStatus::PUBLISH->value,
                    'disabled' => false,
                    'category' => PageCategory::PAGE->value,
                ],
                $customData,
            ),
        );
        foreach ($locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }
        return $page;
    }

    /**
     * Url publique attendue pour une page
     * @param Page $page
     * @param string $locale
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getExpectedUrl(Page $page, string $locale): string
    {
        $siteUrl = $this->container
            ->get(OptionSystemService::class)
            ->getValueByKey(OptionSystem::OS_ADRESSE_SITE->value);
        $category = strtolower($this->container->get(PageService::class)->getAllCategories()[$page->getCategory()]);
        /** @var PageTranslation $translation */
        $translation = $page
            ->getPageTranslations()
            ->filter(fn(PageTranslation $t) => $t->getLocale() === $locale)
            ->first();
        return rtrim($siteUrl, '/') . '/' . $locale . '/' . $category . '/' . $translation->getUrl();
    }

    /**
     * Test méthode parseMarkdown() sur un lien interne valide
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testParseMarkdownInternalLink(): void
    {
        $locale = $this->markdownEditorService->getLocales()['current'];
        $page = $this->createLinkablePage([$locale]);
        $expectedUrl = $this->getExpectedUrl($page, $locale);

        $id = $page->getId();
        $result = $this->markdownEditorService->parseMarkdown(
            "[lien](P#$id) et [encore](P#$id \"titre\") mais pas P#$id dans le texte",
        );

        $this->assertEquals(
            "[lien]($expectedUrl) et [encore]($expectedUrl \"titre\") mais pas P#$id dans le texte",
            $result,
        );
    }

    /**
     * Test méthode parseMarkdown() avec une locale explicite
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testParseMarkdownInternalLinkWithLocale(): void
    {
        $page = $this->createLinkablePage(['fr', 'en']);

        $result = $this->markdownEditorService->parseMarkdown('[a](P#' . $page->getId() . ')', 'en');
        $this->assertEquals('[a](' . $this->getExpectedUrl($page, 'en') . ')', $result);
    }

    /**
     * Test méthode parseMarkdown() sur une page inexistante, non traduite, non publiée ou désactivée
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testParseMarkdownInternalLinkNotLinkable(): void
    {
        $locale = $this->markdownEditorService->getLocales()['current'];
        $notTranslated = $this->createLinkablePage([]);
        $draft = $this->createLinkablePage([$locale], ['status' => PageStatus::DRAFT->value]);
        $archived = $this->createLinkablePage([$locale], ['status' => PageStatus::ARCHIVED->value]);
        $disabled = $this->createLinkablePage([$locale], ['disabled' => true]);

        $result = $this->markdownEditorService->parseMarkdown(
            sprintf(
                '[a](P#999999999) [b](P#%d) [c](P#%d) [d](P#%d) [e](P#%d)',
                $notTranslated->getId(),
                $draft->getId(),
                $archived->getId(),
                $disabled->getId(),
            ),
        );
        $this->assertEquals('[a](#) [b](#) [c](#) [d](#) [e](#)', $result);

        $this->assertEquals('texte sans lien', $this->markdownEditorService->parseMarkdown('texte sans lien'));
    }

    /**
     * Test méthode parseMarkdown() avec conversion des urls relatives en urls absolues
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testParseMarkdownAbsoluteUrls(): void
    {
        $siteUrl = rtrim(
            (string) $this->container
                ->get(OptionSystemService::class)
                ->getValueByKey(OptionSystem::OS_ADRESSE_SITE->value),
            '/',
        );
        $markdown =
            '![img](/assets/a.png) [doc](</assets/my file.pdf>) [ext](https://x.fr/a) [cdn](//cdn.fr/b) texte /brut';

        $this->assertEquals($markdown, $this->markdownEditorService->parseMarkdown($markdown));
        $this->assertEquals(
            "![img]($siteUrl/assets/a.png) [doc](<$siteUrl/assets/my file.pdf>) [ext](https://x.fr/a) [cdn](//cdn.fr/b) texte /brut",
            $this->markdownEditorService->parseMarkdown($markdown, absoluteUrls: true),
        );
    }
}
