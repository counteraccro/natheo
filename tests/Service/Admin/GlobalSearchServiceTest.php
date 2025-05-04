<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du service GlobalService
 */

namespace App\Tests\Service\Admin;

use App\Entity\Admin\Content\Page\Page;
use App\Service\Admin\GlobalSearchService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GlobalSearchServiceTest extends AppWebTestCase
{
    /**
     * @var GlobalSearchService
     */
    private GlobalSearchService $searchService;

    public function setUp(): void
    {
        parent::setUp();
        $this->searchService = $this->container->get(GlobalSearchService::class);
    }

    /**
     * Test méthode globalSearch()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGlobalSearch(): void
    {
        $page = $this->createPageAllDataDefault();
        $this->createPageAllDataDefault();
        $title = $page->getPageTranslationByLocale('fr')->getTitre();

        $result = $this->searchService->globalSearch('page', $title, 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertCount(1, $result['elements']);
        $this->assertArrayHasKey('id', $result['elements'][0]);
        $this->assertEquals($page->getId(), $result['elements'][0]['id']);

        $faq = $this->createFaqAllDataDefault();
        $this->createFaqAllDataDefault();
        $title = $faq->getFaqTranslationByLocale('fr')->getTitle();
        $result = $this->searchService->globalSearch('faq', $title, 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertCount(1, $result['elements']);
        $this->assertArrayHasKey('id', $result['elements'][0]);
        $this->assertEquals($faq->getId(), $result['elements'][0]['id']);

        $menu = $this->createMenuAllDataDefault();
        $this->createMenuAllDataDefault();
        $title = $menu->getName();
        $result = $this->searchService->globalSearch('menu', $title, 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertCount(1, $result['elements']);
        $this->assertArrayHasKey('id', $result['elements'][0]);
        $this->assertEquals($menu->getId(), $result['elements'][0]['id']);

        $tag = $this->createTag();
        foreach ($this->locales as$locale) {
            $this->createTagTranslation($tag, ['locale' => $locale]);
        }

        $tag2 = $this->createTag();
        foreach ($this->locales as$locale) {
            $this->createTagTranslation($tag2, ['locale' => $locale]);
        }

        $result = $this->searchService->globalSearch('tag', '', 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertCount(2, $result['elements']);
        $this->assertArrayHasKey('id', $result['elements'][0]);
        $this->assertEquals($tag->getId(), $result['elements'][0]['id']);

        $user = $this->createUser();
        $this->createUserAdmin();
        $result = $this->searchService->globalSearch('user', $user->getEmail(), 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertCount(1, $result['elements']);
        $this->assertArrayHasKey('id', $result['elements'][0]);
        $this->assertEquals($user->getId(), $result['elements'][0]['id']);
    }
}