<?php
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
        for ($i = 0; $i < 10; $i++) {
            $tag = $this->createTag();
            for ($j = 0; $j < 3; $j++) {
                $this->createTagTranslation($tag);
            }
        }

        $result = $this->tagService->getAllPaginate(1, 7, []);
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(7, $result->getIterator()->count());
        $this->assertEquals(10, $result->count());

        $search = $tag->getTagTranslations()->first()->getLabel();
        $result = $this->tagService->getAllPaginate(1, 7, ['search' => $search]);
        $this->assertEquals(1, $result->getIterator()->count());
    }

    /**
     * Test méthode searchByLocale()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSearchByLocale(): void
    {
        $tag = $this->createTag(['disabled' => false]);
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
}
