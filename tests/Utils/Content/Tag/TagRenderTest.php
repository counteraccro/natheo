<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test class pour le rendu des tags
 */

namespace App\Tests\Utils\Content\Tag;

use App\Tests\AppWebTestCase;
use App\Utils\Content\Tag\TagRender;

class TagRenderTest extends AppWebTestCase
{
    /**
     * Test méthode getHtml()
     * @return void
     */
    public function testGetHtml(): void
    {
        $tag = $this->createTag();
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale]);
        }

        $locale = 'fr';
        $label = $tag->getTagTranslationByLocale($locale)->getLabel();
        $tagRender = new TagRender($tag, $locale);
        $html = $tagRender->getHtml();
        $this->assertIsString($html);
        $this->assertStringContainsString('<span class="badge rounded-pill badge-nat"', $html);
        $this->assertStringContainsString($tag->getColor(), $html);
        $this->assertStringContainsString($label, $html);
    }
}