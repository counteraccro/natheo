<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test TagService
 */

namespace App\Tests\Service\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use App\Service\Admin\Content\Tag\TagService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class TagServiceTest extends AppWebTestCase
{
    /**
     * @var TagService|mixed|object|Container|null
     */
    private TagService $tagService;

    public function setUp(): void
    {
        parent::setUp();
        $this->tagService = $this->container->get(TagService::class);
    }

    /**
     * Test méthode searchByLocale()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSearchByLocale(): void
    {
        $tag = $this->createTag(null, ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale, 'label' => $locale . ' Un label']);
        }

        $tagTranslation = $tag->getTagTranslationByLocale($this->locales[0]);
        $string = $tagTranslation->getLabel();

        $result = $this->tagService->searchByLocale($this->locales[0], $string);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        // Les jokers LIKE saisis sont recherchés littéralement
        $this->assertCount(0, $this->tagService->searchByLocale($this->locales[0], '%'));
        $this->assertCount(0, $this->tagService->searchByLocale($this->locales[0], '_'));
    }

    /**
     * Test méthode newTagByNameAndLocale()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNewTagByNameAndLocale(): void
    {
        $user = $this->createUserContributeur();
        $tag = $this->tagService->newTagByNameAndLocale('fr', 'nouveau-tag', $user);
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals($user->getId(), $tag->getUser()?->getId());
        $this->assertMatchesRegularExpression(Tag::COLOR_PATTERN, $tag->getColor());
        $this->assertEquals('nouveau-tag', $tag->getTagTranslationByLocale('fr')->getLabel());

        $same = $this->tagService->newTagByNameAndLocale('fr', 'nouveau-tag', $this->createUserContributeur());
        $this->assertEquals($tag->getId(), $same->getId());
        $this->assertEquals($user->getId(), $same->getUser()?->getId());

        $this->assertEquals('nouveau-tag (en)', $tag->getTagTranslationByLocale('en')->getLabel());

        // Libellé repris sans tenir compte de la casse
        $this->assertEquals($tag->getId(), $this->tagService->newTagByNameAndLocale('fr', 'NOUVEAU-TAG')?->getId());

        // Libellés générés déjà pris : suffixe numéroté au lieu d'un conflit d'unicité
        $fromEn = $this->tagService->newTagByNameAndLocale('en', 'nouveau-tag', $user);
        $this->assertInstanceOf(Tag::class, $fromEn);
        $this->assertNotEquals($tag->getId(), $fromEn->getId());
        $this->assertEquals('nouveau-tag (es 2)', $fromEn->getTagTranslationByLocale('es')->getLabel());
        $this->assertEquals('nouveau-tag (fr)', $fromEn->getTagTranslationByLocale('fr')->getLabel());

        $tag->setDisabled(true);
        $this->persistAndFlush($tag);
        $this->assertNull($this->tagService->newTagByNameAndLocale('fr', 'nouveau-tag', $user));
    }
}
