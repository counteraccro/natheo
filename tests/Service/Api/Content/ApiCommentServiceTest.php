<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Service\Api\Content;

use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Entity\Admin\Content\Comment\Comment;
use App\Service\Api\Content\ApiCommentService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Comment\CommentConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function testGetCommentByPageIdOrSlug()
    {

        $translator = $this->container->get(TranslatorInterface::class);
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

        $user = $this->createUserContributeur();
        // Test ID
        $dto = new ApiCommentByPageDto($page->getId(), '', 'fr', 1, 10, 'id', 'asc', '');
        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('status', $result[0]);
        $this->assertArrayHasKey('createdAt', $result[0]);
        $this->assertArrayHasKey('updateAt', $result[0]);
        $this->assertArrayHasKey('comment', $result[0]);

        $status1 = $status2 = $status3 = 0;
        foreach ($result as $comment) {
            switch ($comment['status']) {
                case CommentConst::WAIT_VALIDATION:
                    $status1++;
                    $this->assertNotEquals($translator->trans('api_errors.comment.wait.validation', domain: 'api_errors'), $comment['comment']);
                    break;
                case CommentConst::MODERATE:
                    $status3++;
                    $this->assertNotEquals($translator->trans('api_errors.comment.moderate', domain: 'api_errors'), $comment['comment']);
                    $this->assertArrayHasKey('moderate', $comment);
                    break;
                case CommentConst::VALIDATE:
                    $status2++;
                    break;
                default:
                    break;
            }
        }

        $this->assertEquals(2, $status1);
        $this->assertEquals(4, $status2);
        $this->assertEquals(4, $status3);

        $user = $this->createUser();
        // Test slug
        $dto = new ApiCommentByPageDto(0, $page->getPageTranslationByLocale('fr')->getUrl(), 'fr', 1, 10, 'id', 'asc', '');
        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $user);
        $status1 = $status2 = $status3 = 0;
        foreach ($result as $comment) {
            switch ($comment['status']) {
                case CommentConst::WAIT_VALIDATION:
                    $status1++;
                    $this->assertEquals($translator->trans('api_errors.comment.wait.validation', domain: 'api_errors'), $comment['comment']);
                    break;
                case CommentConst::MODERATE:
                    $status3++;
                    $this->assertEquals($translator->trans('api_errors.comment.moderate', domain: 'api_errors'), $comment['comment']);
                    $this->assertArrayNotHasKey('moderate', $comment);
                    break;
                case CommentConst::VALIDATE:
                    $status2++;
                    break;
                default:
                    break;
            }
        }

        $this->assertEquals(2, $status1);
        $this->assertEquals(4, $status2);
        $this->assertEquals(4, $status3);
    }
}