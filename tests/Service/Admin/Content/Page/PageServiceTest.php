<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Test pageService
 */

namespace App\Tests\Service\Admin\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Service\Admin\Content\Page\PageService;
use App\Service\Admin\System\TranslateService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Page\PageConst;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageServiceTest extends AppWebTestCase
{
    /**
     * @var PageService
     */
    private PageService $pageService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->pageService = $this->container->get(PageService::class);
    }

    /**
     * Test méthode getAllPaginate
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate(): void
    {
        $user = $this->createUserContributeur();
        $user2 = $this->createUserContributeur();

        $page = $this->createPage($user);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }
        $this->createPage($user);
        $this->createPage($user2);

        $result = $this->pageService->getAllPaginate(1, 20);
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(3, $result->count());

        $result = $this->pageService->getAllPaginate(1, 20, userId: $user->getId());
        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(2, $result->count());
    }

    /**
     * Test méthode getStatusStr()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetStatusStr(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $result = $this->pageService->getStatusStr(PageConst::STATUS_DRAFT);
        $this->assertIsString($result);
        $this->assertEquals($translator->trans('page.status.draft', domain: 'page'), $result);

        $result = $this->pageService->getStatusStr(PageConst::STATUS_PUBLISH);
        $this->assertIsString($result);
        $this->assertEquals($translator->trans('page.status.publish', domain: 'page'), $result);

        $result = $this->pageService->getStatusStr(1000);
        $this->assertIsString($result);
        $this->assertEquals($translator->trans('page.status.inconnu', domain: 'page'), $result);
    }

    /**
     * Test la methode getAllStatus()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllStatus(): void
    {
        $result = $this->pageService->getAllStatus();
        $this->assertIsArray($result);
    }

    /**
     * Test la méthode getAllRender()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllRender(): void
    {
        $result = $this->pageService->getAllRender();
        $this->assertIsArray($result);
    }

    /**
     * Test méthode getAllContent()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllContent(): void
    {
        $result = $this->pageService->getAllContent();
        $this->assertIsArray($result);
    }

    /**
     * Test méthode getAllCategories()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllCategories(): void
    {
        $result = $this->pageService->getAllCategories();
        $this->assertIsArray($result);
    }

    /**
     * Test méthode getCategoryById()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetCategoryById(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $result = $this->pageService->getCategoryById(PageConst::PAGE_CATEGORY_PAGE);
        $this->assertIsString($result);
        $this->assertEquals($translator->trans('page.category.page', domain: 'page'), $result);

        $result = $this->pageService->getCategoryById(PageConst::PAGE_CATEGORY_FAQ);
        $this->assertIsString($result);
        $this->assertEquals($translator->trans('page.category.faq', domain: 'page'), $result);
    }

    /**
     * Test méthode getDiffBetweenHistoryAndPage()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetDiffBetweenHistoryAndPage(): void
    {
        $user = $this->createUserContributeur();
        $page = $this->createPageAllDataDefault($user);

        $this->client->loginUser($user, 'admin');

        $result = $this->pageService->getDiffBetweenHistoryAndPage($page);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('show_msg', $result);
        $this->assertFalse($result['show_msg']);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('msg', $result);
    }

    /**
     * Test méthode isUniqueUrl()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testIsUniqueUrl(): void
    {
        $page = $this->createPageAllDataDefault();
        $result = $this->pageService->isUniqueUrl('url-demo', $page->getId());
        $this->assertFalse($result);
    }

    /**
     * Test méthode getListeContentByType()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListeContentByType(): void
    {
        $this->createFaqAllDataDefault();
        $this->createFaqAllDataDefault();

        $faq = $this->createFaq(customData: ['disabled' => true]);
        foreach ($this->locales as $locale) {
            $data = ['locale' => $locale];
            $this->createFaqTranslation($faq, $data);
        }

        $result = $this->pageService->getListeContentByType(PageConst::CONTENT_TYPE_FAQ);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertCount(2, $result['list']);
        $this->assertArrayHasKey('selected', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('help', $result);

        $result = $this->pageService->getListeContentByType(PageConst::CONTENT_TYPE_LISTING);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('selected', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('help', $result);
    }

    /**
     * Test méthode getInfoContentByTypeAndTypeId()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetInfoContentByTypeAndTypeId(): void
    {
        $page = $this->createPageAllDataDefault();

        foreach ($page->getPageContents() as $content) {
            if ($content->getType() === PageConst::CONTENT_TYPE_FAQ) {
                $result = $this->pageService->getInfoContentByTypeAndTypeId($content->getType(), $content->getTypeId());
                $this->assertIsArray($result);
                $this->assertArrayHasKey('type', $result);
                $this->assertArrayHasKey('info', $result);
            }
        }
    }

    /**
     * Test méthode getAllTitleAndUrlPage()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllTitleAndUrlPage(): void
    {
        $page1 = $this->createPageAllDataDefault();
        $page2 = $this->createPageAllDataDefault();
        $page3 = $this->createPageAllDataDefault();

        $result = $this->pageService->getAllTitleAndUrlPage();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey($page1->getId(), $result);
        $this->assertArrayHasKey($page2->getId(), $result);
        $this->assertArrayHasKey($page3->getId(), $result);
        foreach ($this->locales as $locale) {
            $this->assertArrayHasKey($locale, $result[$page1->getId()]);
            $this->assertArrayHasKey('title', $result[$page1->getId()][$locale]);
            $this->assertArrayHasKey('url', $result[$page1->getId()][$locale]);
        }
    }

    /**
     * Test méthode switchLandingPage()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSwitchLandingPage(): void
    {
        $page1 = $this->createPage();
        $this->createPage();
        $this->createPage();
        $this->createPage(customData: ['landingPage' => true]);

        $this->pageService->switchLandingPage($page1->getId());

        $this->em->clear();

        $pages = $this->pageService->findBy(Page::class);
        foreach ($pages as $page) {
            $this->assertFalse($page->isLandingPage());
        }
    }

    /**
     * Test méthode getFormatedListePageForInternalLink()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetFormatedListePageForInternalLink(): void
    {

        $page = $this->createPage(customData: ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }

        $page2 = $this->createPage(customData: ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page2, ['locale' => $locale]);
        }

        $page3 = $this->createPage(customData: ['disabled' => true]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page3, ['locale' => $locale]);
        }

        $result = $this->pageService->getFormatedListePageForInternalLink('fr');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey($page->getId(), $result);
        $this->assertArrayHasKey($page2->getId(), $result);
        $this->assertArrayHasKey('title', $result[$page->getId()]);
        $this->assertArrayHasKey('id', $result[$page->getId()]);
    }

    /**
     * Test méthode getListeTitlePageByLocale()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetListeTitlePageByLocale() :void {
        $page = $this->createPage(customData: ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }

        $page2 = $this->createPage(customData: ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page2, ['locale' => $locale]);
        }

        $page3 = $this->createPage(customData: ['disabled' => true]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page3, ['locale' => $locale]);
        }

        $result = $this->pageService->getListeTitlePageByLocale('fr');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey($page->getId(), $result);
        $this->assertArrayHasKey($page2->getId(), $result);
        $this->assertArrayHasKey($page3->getId(), $result);
    }
}