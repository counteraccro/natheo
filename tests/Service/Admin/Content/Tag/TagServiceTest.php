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
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * Test de la méthode getAllPaginate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate(): void
    {
        $user = $this->createUserContributeur();
        for ($i = 0; $i < 10; $i++) {
            $tag = $this->createTag($i < 4 ? $user : null);
            foreach ($this->locales as $locale) {
                $this->createTagTranslation($tag, ['locale' => $locale]);
            }
        }

        $result = $this->tagService->getAllPaginate(1, 7, []);
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(7, $result->getIterator()->count());
        $this->assertEquals(10, $result->count());

        $search = $tag->getTagTranslationByLocale('fr')->getLabel();
        $result = $this->tagService->getAllPaginate(1, 7, ['search' => $search, 'locale' => 'fr']);
        $this->assertEquals(1, $result->getIterator()->count());

        // La recherche porte uniquement sur la locale courante
        $result = $this->tagService->getAllPaginate(1, 7, ['search' => $search, 'locale' => 'en']);
        $this->assertEquals(0, $result->count());

        // Tri par libellé : une ligne par tag
        $result = $this->tagService->getAllPaginate(1, 20, ['orderField' => 'label', 'locale' => 'fr']);
        $this->assertEquals(10, $result->count());

        $result = $this->tagService->getAllPaginate(1, 20, [], $user->getId());
        $this->assertEquals(4, $result->count());
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
    }

    /**
     * Test méthode searchByNameByLocale()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSearchByNameByLocale(): void
    {
        $string = self::getFaker()->text(10);
        $tag = $this->tagService->searchByNameByLocale($this->locales[0], $string);
        $this->assertNull($tag);

        $tag = $this->createTag();
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale, 'label' => $locale . ' Un label']);
        }

        $tagTranslation = $tag->getTagTranslationByLocale($this->locales[0]);
        $string = $tagTranslation->getLabel();

        $result = $this->tagService->searchByNameByLocale($this->locales[0], $string);
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals($tag->getId(), $result->getId());
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

        $tag->setDisabled(true);
        $this->persistAndFlush($tag);
        $this->assertNull($this->tagService->newTagByNameAndLocale('fr', 'nouveau-tag', $user));
    }

    /**
     * Test méthodes populate() et validate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testPopulateAndValidate(): void
    {
        $existing = $this->createTag();
        $this->createTagTranslation($existing, ['locale' => 'fr', 'label' => 'deja-pris']);

        $owner = $this->createUserContributeur();
        $tag = new Tag();
        $tag->setUser($owner);
        $this->tagService->populate($tag, [
            'color' => '#AbCdEf',
            'disabled' => true,
            'user' => ['id' => 999],
            'createdAt' => '2000-01-01',
            'tagTranslations' => [
                ['locale' => 'fr', 'label' => ' libelle '],
                ['locale' => 'en', 'label' => 'label'],
                ['locale' => 'xx', 'label' => 'ignore'],
            ],
        ]);
        $this->assertEquals('#AbCdEf', $tag->getColor());
        $this->assertTrue($tag->isDisabled());
        $this->assertSame($owner, $tag->getUser());
        $this->assertNull($tag->getCreatedAt());
        $this->assertCount(2, $tag->getTagTranslations());
        $this->assertEquals('libelle', $tag->getTagTranslationByLocale('fr')->getLabel());
        $this->assertEmpty($this->tagService->validate($tag));

        $tag->setColor('#fff');
        $tag->getTagTranslationByLocale('fr')->setLabel('deja-pris');
        $tag->getTagTranslationByLocale('en')->setLabel(str_repeat('a', 256));
        $this->assertCount(3, $this->tagService->validate($tag));
    }

    /**
     * Test méthode countPages()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCountPages(): void
    {
        $tag = $this->createTag();
        $this->assertEquals(0, $this->tagService->countPages($tag));
        $this->createPageTag(tag: $tag);
        $this->assertEquals(1, $this->tagService->countPages($tag));
    }
}
