<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test class pour le rendu des tags
 */

namespace App\Tests\Service\Admin\Content\Tag;

use App\Tests\AppWebTestCase;
use App\Service\Admin\Content\Tag\TagRender;

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
        $tagRender = $this->container->get(TagRender::class);
        $html = $tagRender->getHtml($tag, $locale);
        $this->assertStringContainsString('<span class="badge rounded-pill badge-nat"', $html);
        $this->assertStringContainsString($tag->getColor(), $html);
        $this->assertStringContainsString(htmlspecialchars($label, ENT_QUOTES), $html);
    }

    /**
     * Le libellé est échappé et une couleur invalide est remplacée par la couleur par défaut
     * @return void
     */
    public function testGetHtmlEscape(): void
    {
        $tag = $this->createTag(null, ['color' => '"><img']);
        $this->createTagTranslation($tag, ['locale' => 'fr', 'label' => '<b onclick="x">tag</b>']);

        $html = $this->container->get(TagRender::class)->getHtml($tag, 'fr');
        $this->assertStringNotContainsString('<b', $html);
        $this->assertStringNotContainsString('"><img', $html);
        $this->assertStringContainsString('&lt;b onclick=&quot;x&quot;&gt;tag&lt;/b&gt;', $html);
        $this->assertStringContainsString(TagRender::DEFAULT_COLOR, $html);
    }
}
