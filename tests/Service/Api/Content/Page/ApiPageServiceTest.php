<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test ApiPageService
 */

namespace Service\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageCategoryDto;
use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Dto\Api\Content\Page\ApiFindPageTagDto;
use App\Service\Api\Content\Page\ApiPageService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container;

class ApiPageServiceTest extends AppWebTestCase
{
    /**
     * @var ApiPageService|mixed|object|Container|null
     */
    private ApiPageService $apiPageService;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiPageService = $this->container->get(ApiPageService::class);
    }

    /**
     * Test méthode getPageForApi()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetPageForApi(): void
    {

        $page = $this->createPageAllDataDefault();
        $slug = $page->getPageTranslationByLocale('fr')->getUrl();
        $dto = new ApiFindPageDto($slug, 'fr', false, false, true, [0], "");

        $result = $this->apiPageService->getPageForApi($dto);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('title', $result);
        $this->assertEquals($page->getPageTranslationByLocale('fr')->getTitre(), $result['title']);
        $this->assertArrayHasKey('render', $result);
        $this->assertEquals($page->getRender(), $result['render']);
        $this->assertArrayHasKey('author', $result);
        $this->assertEquals($page->getUser()->getEmail(), $result['author']);
        $this->assertArrayHasKey('statistiques', $result);
        $this->assertArrayHasKey('contents', $result);
        $this->assertCount($page->getPageContents()->count(), $result['contents']);
    }

    /**
     * test méthode getListingPageByCategoryForApi()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListingPageByCategoryForApi(): void
    {
        $this->createPageAllDataDefault();
        $this->createPageAllDataDefault();
        $dto = new ApiFindPageCategoryDto('Page', 'fr', 1, 10, '');
        $result = $this->apiPageService->getListingPageByCategoryForApi($dto);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('pages', $result);
        $this->assertCount(2, $result['pages']);
        $this->assertArrayHasKey('limit', $result);
        $this->assertEquals(10, $result['limit']);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertEquals(1, $result['current_page']);
        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals(2, $result['rows']);
    }

    /**
     * Test méthode getListingPagesByTag()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListingPagesByTag(): void
    {
        $page = $this->createPageAllDataDefault();
        $tag = $this->createTag(['disabled' => false]);
        foreach($this->locales as $locale)
        {
            $this->createTagTranslation($tag, ['locale' => $locale, 'label' => 'unit-tag-' . $locale]);
        }
        $page->addTag($tag);
        $tag->addPage($page);
        $this->em->persist($page);
        $this->em->flush();

        $dto = new ApiFindPageTagDto('unit-tag-fr', 'fr', 1, 10, "");
        $result = $this->apiPageService->getListingPagesByTag($dto);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('pages', $result);
        $this->assertCount(1, $result['pages']);
        $this->assertArrayHasKey('limit', $result);
        $this->assertEquals(10, $result['limit']);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertEquals(1, $result['current_page']);
        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals(1, $result['rows']);

        $dto = new ApiFindPageTagDto('unit-tag-no-exist-fr', 'fr', 1, 10, "");
        $result = $this->apiPageService->getListingPagesByTag($dto);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('pages', $result);
        $this->assertCount(0, $result['pages']);
        $this->assertArrayHasKey('limit', $result);
        $this->assertEquals(10, $result['limit']);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertEquals(1, $result['current_page']);
        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals(0, $result['rows']);
    }
}