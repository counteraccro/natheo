<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test mde PagePopulate
 */

namespace App\Tests\Utils\Content\Page;

use App\Service\Admin\Content\Page\PageService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Page\PagePopulate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PagePopulateTest extends AppWebTestCase
{
    /**
     * Test de la méthode populate
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testPopulate(): void
    {
        $page = $this->createPageAllDataDefault();
        $tag1 = $this->createTag();
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag1, ['locale' => $locale, 'label' => 'tag-unit-test-' . $locale]);
        }

        $tag2 = $this->createTag();
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag2, ['locale' => $locale, 'label' => 'tag2-unit-test-' . $locale]);
        }

        $tag3 = $this->createTag();
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag3, ['locale' => $locale, 'label' => 'tag3-unit-test-' . $locale]);
        }

        $menu = $this->createMenu();

        $data = [
            'id' => $page->getId(),
            'render' => 1,
            'status' => 1,
            'pageTranslations' => [
                [
                    'id' => 1,
                    'page' => 1,
                    'locale' => 'fr',
                    'titre' => 'unit-test-page-fr',
                    'url' => 'pages',
                ],
                [
                    'id' => 2,
                    'page' => 1,
                    'locale' => 'es',
                    'titre' => 'unit-test-page-es',
                    'url' => 'paginas',
                ],
                [
                    'id' => 3,
                    'page' => 1,
                    'locale' => 'en',
                    'titre' => 'unit-test-page-en',
                    'url' => 'page',
                ],
            ],
            'pageContents' => [
                [
                    'id' => 23,
                    'page' => 1,
                    'renderOrder' => 1,
                    'type' => 1,
                    'pageContentTranslations' => [
                        [
                            'id' => 37,
                            'pageContent' => 23,
                            'locale' => 'fr',
                            'text' => 'unit-test-content-fr',
                        ],
                        [
                            'id' => 38,
                            'pageContent' => 23,
                            'locale' => 'en',
                            'text' => 'unit-test-content-en',
                        ],
                        [
                            'id' => 39,
                            'pageContent' => 23,
                            'locale' => 'es',
                            'text' => 'unit-test-content-es',
                        ],
                    ],
                    'typeId' => null,
                    'renderBlock' => 1,
                ],
            ],
            'pageStatistiques' => [
                [
                    'id' => 1,
                    'page' => 1,
                    'key' => 'PAGE_NB_VISITEUR',
                    'value' => '9999',
                ],
                [
                    'id' => 2,
                    'page' => 1,
                    'key' => 'PAGE_NB_READ',
                    'value' => '9999',
                ],
            ],
            'disabled' => false,
            'category' => 1,
            'landingPage' => false,
            'openComment' => true,
            'nbComment' => 0,
            'ruleComment' => 2,
            'headerImg' => null,
            'menus' => [
                0 => $menu->getId(),
            ],
            'tags' => [
                [
                    'id' => $tag1->getId(),
                    'color' => '#6F42C1',
                    'disabled' => false,
                    'tagTranslations' => [
                        [
                            'id' => 1,
                            'tag' => 1,
                            'locale' => 'fr',
                            'label' => 'Natheo',
                        ],
                        [
                            'id' => 2,
                            'tag' => 1,
                            'locale' => 'es',
                            'label' => 'Natheo',
                        ],
                        [
                            'id' => 3,
                            'tag' => 1,
                            'locale' => 'en',
                            'label' => 'Natheo',
                        ],
                    ],
                ],
                [
                    'id' => $tag2->getId(),
                    'color' => '#23e515',
                    'disabled' => false,
                    'tagTranslations' => [
                        [
                            'id' => 22,
                            'tag' => 8,
                            'locale' => 'fr',
                            'label' => 'Page',
                        ],
                        [
                            'id' => 23,
                            'tag' => 8,
                            'locale' => 'es',
                            'label' => 'Page (ES)',
                        ],
                        [
                            'id' => 24,
                            'tag' => 8,
                            'locale' => 'en',
                            'label' => 'Page (EN)',
                        ],
                    ],
                ],
                [
                    'id' => $tag3->getId(),
                    'color' => '#00478c',
                    'disabled' => false,
                    'tagTranslations' => [
                        [
                            'id' => 28,
                            'tag' => 10,
                            'locale' => 'fr',
                            'label' => 'toto',
                        ],
                        [
                            'id' => 29,
                            'tag' => 10,
                            'locale' => 'en',
                            'label' => 'toto (en)',
                        ],
                        [
                            'id' => 30,
                            'tag' => 10,
                            'locale' => 'es',
                            'label' => 'toto (es)',
                        ],
                    ],
                ],
            ],
        ];

        $pageService = $this->container->get(PageService::class);
        $pagePopulate = new PagePopulate($page, $data, $pageService);
        $page = $pagePopulate->populate()->getPage();

        foreach ($this->locales as $locale) {
            $pageTranslation = $page->getPageTranslationByLocale($locale);
            $this->assertEquals('unit-test-page-' . $locale, $pageTranslation->getTitre());
        }

        $this->assertCount(1, $page->getPageContents());
        $pageContent = $page->getPageContents()->first();

        foreach ($this->locales as $locale) {
            $pageContentTranslation = $pageContent->getPageContentTranslationByLocale($locale);
            $this->assertEquals('unit-test-content-' . $locale, $pageContentTranslation->getText());
        }

        foreach ($page->getTags() as $tag) {
            switch ($tag->getId()) {
                case $tag1->getId():
                    foreach ($this->locales as $locale) {
                        $tagTranslation = $tag->getTagTranslationByLocale($locale);
                        $this->assertEquals('tag-unit-test-' . $locale, $tagTranslation->getLabel());
                    }
                    break;
                case $tag2->getId():
                    foreach ($this->locales as $locale) {
                        $tagTranslation = $tag->getTagTranslationByLocale($locale);
                        $this->assertEquals('tag2-unit-test-' . $locale, $tagTranslation->getLabel());
                    }
                    break;
                case $tag3->getId():
                    foreach ($this->locales as $locale) {
                        $tagTranslation = $tag->getTagTranslationByLocale($locale);
                        $this->assertEquals('tag3-unit-test-' . $locale, $tagTranslation->getLabel());
                    }
                    break;
            }
        }
        $this->assertCount(1, $page->getMenus());
    }
}
