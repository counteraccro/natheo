<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test CommentService
 */

namespace App\Tests\Service\Admin\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use App\Enum\Admin\Comment\Status;
use App\Service\Admin\Content\Comment\CommentService;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Comment\CommentConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentServiceTest extends AppWebTestCase
{
    private CommentService $commentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentService = $this->container->get(CommentService::class);
    }

    /**
     * Test méthode getAllPaginate()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllPaginate(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $comment = $this->createComment();
        }

        $commentModerate = $this->createComment(userModeration: $this->createUserContributeur());

        $result = $this->commentService->getAllPaginate(1, 4);
        $this->assertEquals(4, $result->getIterator()->count());
        $this->assertEquals(6, $result->count());

        $result = $this->commentService->getAllPaginate(1, 4, $comment->getComment());
        $this->assertEquals(1, $result->getIterator()->count());
        $this->assertEquals(1, $result->count());

        $result = $this->commentService->getAllPaginate(1, 4, userId: $commentModerate->getUserModeration()->getId());
        $this->assertEquals(1, $result->getIterator()->count());
        $this->assertEquals(1, $result->count());

        $result = $this->commentService->getAllPaginate(
            1,
            4,
            $comment->getComment(),
            $commentModerate->getUserModeration()->getId(),
        );
        $this->assertEquals(0, $result->count());
    }

    /**
     * Test méthode getStatusFormatedByCode()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetStatusFormatedByCode(): void
    {
        $result = $this->commentService->getStatusFormatedByCode(Status::WAIT_VALIDATION->value);
        $this->assertStringContainsString('badge badge-pending', $result);

        $result = $this->commentService->getStatusFormatedByCode(Status::MODERATE->value);
        $this->assertStringContainsString('badge badge-moderate', $result);

        $result = $this->commentService->getStatusFormatedByCode(Status::VALIDATE->value);
        $this->assertStringContainsString('badge badge-validated', $result);
    }

    /**
     * Test méthode getStatusStringByCode()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetStatusStringByCode(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $result = $this->commentService->getStatusStringByCode(Status::WAIT_VALIDATION->value);
        $this->assertStringContainsString(
            $translator->trans('comment.status.wait.validation', domain: 'comment'),
            $result,
        );

        $result = $this->commentService->getStatusStringByCode(Status::VALIDATE->value);
        $this->assertStringContainsString($translator->trans('comment.status.validate', domain: 'comment'), $result);

        $result = $this->commentService->getStatusStringByCode(Status::MODERATE->value);
        $this->assertStringContainsString($translator->trans('comment.status.moderate', domain: 'comment'), $result);
    }

    /**
     * Test méthode getAllStatus()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllStatus(): void
    {
        $result = $this->commentService->getAllStatus();
        $this->assertIsArray($result);
    }

    /**
     * Test méthode getCommentFilter()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetCommentFilter(): void
    {
        $page = $this->createPage();
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }
        for ($i = 0; $i < 3; $i++) {
            $this->createComment($page, customData: ['status' => Status::WAIT_VALIDATION->value]);
        }
        for ($i = 0; $i < 4; $i++) {
            $this->createComment($page, customData: ['status' => Status::MODERATE->value]);
        }

        for ($i = 0; $i < 5; $i++) {
            $this->createComment($page, customData: ['status' => Status::VALIDATE->value]);
        }

        $result = $this->commentService->getCommentFilter(Status::WAIT_VALIDATION->value, $page->getId(), 1, 2);
        $this->assertArrayHasKey('nb', $result);
        $this->assertEquals(3, $result['nb']);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']);

        $result = $this->commentService->getCommentFilter(Status::MODERATE->value, $page->getId(), 1, 2);
        $this->assertArrayHasKey('nb', $result);
        $this->assertEquals(4, $result['nb']);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']);

        $result = $this->commentService->getCommentFilter(Status::VALIDATE->value, $page->getId(), 1, 2);
        $this->assertArrayHasKey('nb', $result);
        $this->assertEquals(5, $result['nb']);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']);
    }

    /**
     * Test méthode getNbCommentByStatus()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetNbCommentByStatus(): void
    {
        $page = $this->createPage();
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }
        for ($i = 0; $i < 3; $i++) {
            $this->createComment($page, customData: ['status' => Status::WAIT_VALIDATION->value]);
        }
        for ($i = 0; $i < 4; $i++) {
            $this->createComment($page, customData: ['status' => Status::MODERATE->value]);
        }

        for ($i = 0; $i < 5; $i++) {
            $this->createComment($page, customData: ['status' => Status::VALIDATE->value]);
        }

        $result = $this->commentService->getNbCommentByStatus(Status::WAIT_VALIDATION->value);
        $this->assertEquals(3, $result);

        $result = $this->commentService->getNbCommentByStatus(Status::MODERATE->value);
        $this->assertEquals(4, $result);

        $result = $this->commentService->getNbCommentByStatus(Status::VALIDATE->value);
        $this->assertEquals(5, $result);
    }

    /**
     * Test méthode updateMultipleComment()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateMultipleComment(): void
    {
        $user = $this->createUserContributeur();
        $comment1 = $this->createComment(
            customData: ['status' => CommentConst::WAIT_VALIDATION, 'moderationComment' => 'toto'],
        );
        $comment2 = $this->createComment(customData: ['status' => Status::WAIT_VALIDATION->value]);

        $data = [
            'selected' => [$comment1->getId(), $comment2->getId()],
            'status' => Status::VALIDATE->value,
            'moderateComment' => '',
        ];

        $this->commentService->updateMultipleComment($data, $user);
        $repository = $this->em->getRepository(Comment::class);
        $comments = $repository->findAll();
        foreach ($comments as $comment) {
            $this->assertEquals(Status::VALIDATE->value, $comment->getStatus());
            $this->assertNull($comment->getModerationComment());
        }

        $comment3 = $this->createComment(
            customData: ['status' => Status::VALIDATE->value, 'moderationComment' => 'toto'],
        );
        $comment4 = $this->createComment(customData: ['status' => Status::VALIDATE->value]);

        $message = self::getFaker()->text(20);
        $data = [
            'selected' => [$comment1->getId(), $comment2->getId(), $comment3->getId(), $comment4->getId()],
            'status' => Status::MODERATE->value,
            'moderateComment' => $message,
        ];

        $this->commentService->updateMultipleComment($data, $user);
        $repository = $this->em->getRepository(Comment::class);
        $this->em->clear();
        $comments = $repository->findAll();
        foreach ($comments as $comment) {
            $this->assertEquals(Status::MODERATE->value, $comment->getStatus());
            $this->assertEquals($message, $comment->getModerationComment());
        }
    }
}
