<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Test la factory de page
 */

namespace App\Tests\Utils\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageFactory;
use App\Utils\Content\Page\PageStatistiqueKey;

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
        $this->assertEquals(PageConst::CONTENT_TYPE_TEXT, $pageContent->getType());
        $this->assertNull($pageContent->getId());
        $this->assertCount(count($this->locales), $pageContent->getPageContentTranslations());
        foreach ($this->locales as $locale) {
            $pageTranslation = $pageContent->getPageContentTranslationByLocale($locale);
            $this->assertNotNull($pageTranslation);
        }

        $this->assertCount(count(PageStatistiqueKey::getConstants()), $page->getPageStatistiques());
        foreach (PageStatistiqueKey::getConstants() as $key) {
            $pageStatistique = $page->getPageStatistiqueByKey($key);
            $this->assertNotNull($pageStatistique);
        }
    }
}
