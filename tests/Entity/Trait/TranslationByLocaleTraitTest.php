<?php

declare(strict_types=1);
/**
 * Test du repli des get*TranslationByLocale()
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Entity\Trait;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use PHPUnit\Framework\TestCase;

class TranslationByLocaleTraitTest extends TestCase
{
    /**
     * Locale trouvée, puis repli sur la locale par défaut, puis sur la première traduction
     * @return void
     */
    public function testFallback(): void
    {
        $tag = new Tag();
        foreach (['en', 'fr'] as $locale) {
            $tag->addTagTranslation((new TagTranslation())->setLocale($locale)->setLabel('label-' . $locale));
        }

        $this->assertSame('label-fr', $tag->getTagTranslationByLocale('fr')->getLabel());
        $this->assertSame('label-fr', $tag->getTagTranslationByLocale('es', 'fr')->getLabel());
        $this->assertSame('label-en', $tag->getTagTranslationByLocale('es')->getLabel());
        $this->assertSame('label-en', $tag->getTagTranslationByLocale('es', 'de')->getLabel());
    }

    /**
     * Aucune traduction : null au lieu de false
     * @return void
     */
    public function testWithoutTranslation(): void
    {
        $this->assertNull((new Tag())->getTagTranslationByLocale('fr'));
    }
}
