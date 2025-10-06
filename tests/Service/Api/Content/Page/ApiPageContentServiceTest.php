<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du service ApiPageContentService
 */

namespace Service\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageContentDto;
use App\Entity\Admin\Content\Page\PageContent;
use App\Service\Api\Content\Page\ApiPageContentService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Page\PageConst;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiPageContentServiceTest extends AppWebTestCase
{
    /**
     * @var ApiPageContentService
     */
    private ApiPageContentService $apiPageContentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiPageContentService = $this->container->get(ApiPageContentService::class);
    }

    /**
     * Test méthode getPageContentForApi()
     * @return void
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetPageContentForApi(): void
    {
        $page = $this->createPageAllDataDefault();
        $content = $page->getPageContents()->first();

        $dto = new ApiFindPageContentDto($content->getId(), 'fr', 1, 20, '');

        $result = $this->apiPageContentService->getPageContentForApi($dto);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertCount(2, $result['content']);

        $dto = new ApiFindPageContentDto(999, 'fr', 1, 20, '');
        $result = $this->apiPageContentService->getPageContentForApi($dto);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test méthode getFormatContent()
     * @return void
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetFormatContent(): void
    {
        $pageContent = $this->createPageContent();
        foreach ($this->locales as $locale) {
            $this->createPageContentTranslation($pageContent, ['locale' => $locale]);
        }
        $dto = new ApiFindPageContentDto($pageContent->getId(), 'fr', 1, 20, '');

        $result = $this->apiPageContentService->getFormatContent($pageContent, $dto);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertStringContainsString(
            $pageContent->getPageContentTranslationByLocale('fr')->getText(),
            $result['content'],
        );

        $this->createPageAllDataDefault();
        $this->createPageAllDataDefault();

        $pageContent = $this->createPageContent(
            customData: ['type' => PageConst::CONTENT_TYPE_LISTING, 'typeId' => PageConst::PAGE_CATEGORY_PAGE],
        );
        $dto = new ApiFindPageContentDto($pageContent->getId(), 'fr', 1, 20, '');
        $result = $this->apiPageContentService->getFormatContent($pageContent, $dto);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('pages', $result['content']);
        $this->assertCount(2, $result['content']['pages']);
    }
}
