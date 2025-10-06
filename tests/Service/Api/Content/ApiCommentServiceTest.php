<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace Service\Api\Content;

use App\Dto\Api\Content\Comment\ApiAddCommentDto;
use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Dto\Api\Content\Comment\ApiModerateCommentDto;
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
            $this->createComment(
                $page,
                $user,
                customData: ['status' => CommentConst::MODERATE, 'moderationComment' => self::getFaker()->text(40)],
            );
        }

        for ($i = 0; $i < 4; $i++) {
            $this->createComment($page, customData: ['status' => CommentConst::WAIT_VALIDATION]);
        }

        $user = $this->createUserContributeur();
        // Test ID
        $dto = new ApiCommentByPageDto($page->getId(), '', 'fr', 1, 10, 'id', 'asc', '');
        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('comments', $result);
        $this->assertArrayHasKey('id', $result['comments'][0]);
        $this->assertArrayHasKey('status', $result['comments'][0]);
        $this->assertArrayHasKey('createdAt', $result['comments'][0]);
        $this->assertArrayHasKey('updateAt', $result['comments'][0]);
        $this->assertArrayHasKey('comment', $result['comments'][0]);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertEquals(1, $result['current_page']);
        $this->assertEquals(12, $result['rows']);
        $this->assertEquals(10, $result['limit']);

        $status1 = $status2 = $status3 = 0;
        foreach ($result['comments'] as $comment) {
            switch ($comment['status']) {
                case CommentConst::WAIT_VALIDATION:
                    $status1++;
                    $this->assertNotEquals(
                        $translator->trans('api_errors.comment.wait.validation', domain: 'api_errors'),
                        $comment['comment'],
                    );
                    break;
                case CommentConst::MODERATE:
                    $status3++;
                    $this->assertNotEquals(
                        $translator->trans('api_errors.comment.moderate', domain: 'api_errors'),
                        $comment['comment'],
                    );
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
        $dto = new ApiCommentByPageDto(
            0,
            $page->getPageTranslationByLocale('fr')->getUrl(),
            'fr',
            1,
            10,
            'id',
            'asc',
            '',
        );
        $result = $this->apiCommentService->getCommentByPageIdOrSlug($dto, $user);
        $status1 = $status2 = $status3 = 0;
        foreach ($result['comments'] as $comment) {
            switch ($comment['status']) {
                case CommentConst::WAIT_VALIDATION:
                    $status1++;
                    $this->assertEquals(
                        $translator->trans('api_errors.comment.wait.validation', domain: 'api_errors'),
                        $comment['comment'],
                    );
                    break;
                case CommentConst::MODERATE:
                    $status3++;
                    $this->assertEquals(
                        $translator->trans('api_errors.comment.moderate', domain: 'api_errors'),
                        $comment['comment'],
                    );
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

    /**
     * Test méthode addNewComment()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddNewComment()
    {
        $page = $this->createPageAllDataDefault();

        $slug = $page->getPageTranslationByLocale('fr')->getUrl();
        $author = self::getFaker()->name();
        $email = self::getFaker()->email();
        $comment = self::getFaker()->text();
        $ip = self::getFaker()->ipv4();
        $userAgent = self::getFaker()->userAgent();
        $dto = new ApiAddCommentDto(0, $slug, 'fr', $author, $email, $comment, $ip, $userAgent);

        /** @var Comment $result */
        $result = $this->apiCommentService->addNewComment($dto);
        $this->assertInstanceOf(Comment::class, $result);
        $this->assertIsInt($result->getId());
        $this->assertEquals($author, $result->getAuthor());
        $this->assertEquals($email, $result->getEmail());
        $this->assertEquals($comment, $result->getComment());
        $this->assertEquals($ip, $result->getIp());
        $this->assertEquals($userAgent, $result->getUserAgent());
        $this->assertEquals($page->getId(), $result->getPage()->getId());
    }

    /**
     * Test méthode moderateComment
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testModerateCommentService()
    {
        $user = $this->createUserContributeur();
        $comment = $this->createComment();

        $moderate = self::getFaker()->text();
        $status = strval(CommentConst::MODERATE);

        $dto = new ApiModerateCommentDto($status, $moderate, self::getFaker()->iosMobileToken());

        $this->apiCommentService->moderateComment($dto, $comment, $user);

        /** @var Comment $commentVerif */
        $commentVerif = $this->apiCommentService->findOneById(Comment::class, $comment->getId());
        $this->assertEquals(CommentConst::MODERATE, $commentVerif->getStatus());
        $this->assertEquals($moderate, $commentVerif->getModerationComment());
        $this->assertEquals($user->getId(), $commentVerif->getUserModeration()->getId());
    }
}
