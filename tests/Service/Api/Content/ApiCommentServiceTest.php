<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Service\Api\Content;

use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Service\Api\Content\ApiCommentService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Comment\CommentConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiCommentServiceTest extends AppWebTestCase
{
    private ApiCommentService $apiCommentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiCommentService = $this->container->get(ApiCommentService::class);
    }

    /**
     * Test méthode getCommentByPageIdOrSlug()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetCommentByPageIdOrSlug() {
        $page = $this->createPageAllDataDefault();

        for ($i = 0; $i < 4; $i++) {
            $this->createComment($page, customData: ['status' => CommentConst::VALIDATE]);
        }

        $user = $this->createUserContributeur();
        for ($i = 0; $i < 4; $i++) {
            $this->createComment($page, $user, customData: ['status' => CommentConst::MODERATE, 'moderationComment' => self::getFaker()->text(40)]);
        }

        for ($i = 0; $i < 4; $i++) {
            $this->createComment($page, customData: ['status' => CommentConst::WAIT_VALIDATION]);
        }

        // Test ID
        $dto = new ApiCommentByPageDto($page->getId(), '', 'fr', 1, 10, '');
        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $user);

        // Test slug
        $dto = new ApiCommentByPageDto(0, $page->getPageTranslationByLocale('fr')->getUrl(), 'fr', 1, 10, '');
        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $user);

        dd($result);
    }
}