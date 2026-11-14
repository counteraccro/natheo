<?php

declare(strict_types=1);
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
use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Repository\Admin\Content\Page\PageRepository;
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
        $dto = new ApiFindPageDto($slug, 'fr', false, false, true, [0], '');

        $result = $this->apiPageService->getPageForApi($dto);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('title', $result);
        $this->assertEquals($page->getPageTranslationByLocale('fr')->getTitre(), $result['title']);
        $this->assertArrayHasKey('render', $result);
        $this->assertEquals($page->getRender(), $result['render']);
        $this->assertArrayHasKey('author', $result);
        $this->assertIsArray($result['author']);
        $this->assertEquals($page->getUser()->getEmail(), $result['author']['author']);
        $this->assertEquals($page->getUser()->getAvatar(), $result['author']['avatar']);
        $this->assertEquals($page->getUser()->getDescription(), $result['author']['description']);
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
        $tag = $this->createTag(null, ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale, 'label' => 'unit-tag-' . $locale]);
        }
        $page->addTag($tag);
        $tag->addPage($page);
        $this->em->persist($page);
        $this->em->flush();

        $dto = new ApiFindPageTagDto('unit-tag-fr', 'fr', 1, 10, '');
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

        $dto = new ApiFindPageTagDto('unit-tag-no-exist-fr', 'fr', 1, 10, '');
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

    /**
     * Test de PageRepository::getBySlug() : la page dont le slug est celui de la locale demandée est prioritaire
     * @return void
     */
    public function testGetBySlugLocalePriority(): void
    {
        $pageFr = $this->createPage(customData: ['disabled' => false, 'status' => PageStatus::PUBLISH->value]);
        $this->createPageTranslation($pageFr, ['locale' => 'fr', 'url' => 'contact']);
        $pageEn = $this->createPage(customData: ['disabled' => false, 'status' => PageStatus::PUBLISH->value]);
        $this->createPageTranslation($pageEn, ['locale' => 'en', 'url' => 'contact']);
        $this->createPageTranslation($pageEn, ['locale' => 'fr', 'url' => 'nous-contacter']);

        /** @var PageRepository $repository */
        $repository = $this->em->getRepository(Page::class);
        $this->assertEquals($pageFr->getId(), $repository->getBySlug('contact', locale: 'fr')?->getId());
        $this->assertEquals($pageEn->getId(), $repository->getBySlug('contact', locale: 'en')?->getId());
        // Slug inconnu dans la locale : repli sur une autre locale (page non traduite)
        $this->assertEquals($pageEn->getId(), $repository->getBySlug('nous-contacter', locale: 'es')?->getId());
    }

    /**
     * Test méthode findEditablePageBySlug() : page désignée par son slug ou page d'accueil, si l'utilisateur peut la modifier
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFindEditablePageBySlug(): void
    {
        $owner = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $other = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $page = $this->createPageAllDataDefault($owner);
        $home = $this->createPageAllDataDefault($owner);
        $this->persistAndFlush($home->setLandingPage(true));
        $slug = $page->getPageTranslationByLocale('fr')->getUrl();

        $this->assertEquals(
            $page->getId(),
            $this->apiPageService->findEditablePageBySlug($slug, 'fr', $owner)?->getId(),
        );
        $this->assertEquals($home->getId(), $this->apiPageService->findEditablePageBySlug('', 'fr', $owner)?->getId());
        $this->assertNull($this->apiPageService->findEditablePageBySlug($slug, 'fr', $other));
        $this->assertNull($this->apiPageService->findEditablePageBySlug('slug-inconnu', 'fr', $owner));
    }

    /**
     * Test méthode getPreviewPageOf() : brouillon sur demande, page non publiée directement, null sinon
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetPreviewPageOf(): void
    {
        $page = $this->createPageAllDataDefault();
        $this->assertNull($this->apiPageService->getPreviewPageOf($page, true));

        $this->createPageDraft($page);
        $this->assertNull($this->apiPageService->getPreviewPageOf($page, false));
        $preview = $this->apiPageService->getPreviewPageOf($page, true);
        $this->assertNotSame($page, $preview);
        $this->assertEquals('Brouillon fr', $preview->getPageTranslationByLocale('fr')->getTitre());
        $this->apiPageService->releasePreviewPage($preview, $page);

        $unpublished = $this->createPageAllDataDefault();
        $this->persistAndFlush($unpublished->setStatus(PageStatus::DRAFT->value));
        $this->assertSame($unpublished, $this->apiPageService->getPreviewPageOf($unpublished, false));
    }

    /**
     * Test méthode releasePreviewPage() : la copie de travail n'est pas enregistrée, la page reste inchangée
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReleasePreviewPage(): void
    {
        $page = $this->createPageAllDataDefault();
        $menu = $this->createMenu();
        $this->createPageDraft($page, [$menu->getId()]);
        $title = $page->getPageTranslationByLocale('fr')->getTitre();
        $nbPages = $this->em->getRepository(Page::class)->count();

        $preview = $this->apiPageService->getPreviewPageOf($page, true);
        $this->apiPageService->releasePreviewPage($preview, $page);
        $this->assertContains($menu, $preview->getMenus());
        $this->assertNotContains($preview, $menu->getPages());
        $this->em->flush();
        $this->em->clear();

        $this->assertEquals($nbPages, $this->em->getRepository(Page::class)->count());
        $pageInDb = $this->em->getRepository(Page::class)->find($page->getId());
        $this->assertEquals($title, $pageInDb->getPageTranslationByLocale('fr')->getTitre());

        $this->apiPageService->releasePreviewPage($pageInDb, $pageInDb);
        $this->assertSame($pageInDb, $this->em->getRepository(Page::class)->find($page->getId()));
    }
}
