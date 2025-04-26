<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Test pageService
 */

namespace App\Tests\Service\Admin\Content\Page;

use App\Service\Admin\Content\Page\PageService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Page\PageConst;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
     */
    public function testGetStatusStr(): void
    {
       
    }
}