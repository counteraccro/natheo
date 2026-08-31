<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Test la factory de page
 */

namespace App\Tests\Utils\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageStatistics;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Page\PageFactory;

class PageFactoryTest extends AppWebTestCase
{
    /**
     * Test méthode create()
     * @return void
     */
    public function testCreate(): void
    {
        $pageFactory = new PageFactory($this->locales);

        $page = $pageFactory->create()->getPage();
        $this->assertNotNull($page);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(count($this->locales), $page->getPageTranslations());
        foreach ($this->locales as $locale) {
            $pageTranslation = $page->getPageTranslationByLocale($locale);
            $this->assertNotNull($pageTranslation);
        }

        $this->assertCount(1, $page->getPageContents());
        $pageContent = $page->getPageContents()->first();
        $this->assertNotNull($pageContent);
        $this->assertEquals(PageContentType::TEXT->value, $pageContent->getType());
        $this->assertNull($pageContent->getId());
        $this->assertCount(count($this->locales), $pageContent->getPageContentTranslations());
        foreach ($this->locales as $locale) {
            $pageTranslation = $pageContent->getPageContentTranslationByLocale($locale);
            $this->assertNotNull($pageTranslation);
        }

        $this->assertCount(count(PageStatistics::cases()), $page->getPageStatistiques());
        foreach (PageStatistics::cases() as $key) {
            $pageStatistique = $page->getPageStatistiqueByKey($key->value);
            $this->assertNotNull($pageStatistique);
        }
    }
}
