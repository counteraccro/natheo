<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller comment
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Comment\Comment;
use App\Enum\Admin\Comment\Status;
use App\Tests\AppWebTestCase;

class CommentControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_comment_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_comment_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('comment.index.page_title_h1', domain: 'comment'),
        );
    }

    /**
     * Test méthode moderateComments()
     * @return void
     */
    public function testModerateComments(): void
    {
        $this->checkNoAccess('admin_comment_moderate_comments');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_comment_moderate_comments'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('comment.moderate.comments.page_title', domain: 'comment'),
        );
    }

    /**
     * Test méthode filterCommentsModeration()
     * @return void
     */
    public function testFilterCommentsModeration(): void
    {
        $comment1 = $this->createComment(customData: ['status' => Status::WAIT_VALIDATION->value]);
        $comment2 = $this->createComment(customData: ['status' => Status::WAIT_VALIDATION->value]);

        $this->checkNoAccess('admin_comment_moderate_comments_filter');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_comment_moderate_comments_filter'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('nb', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertCount(2, $content['data']);
        $commentVerif = $content['data'][0];
        $this->assertArrayHasKey('id', $commentVerif);
        $this->assertEquals($comment2->getId(), $commentVerif['id']);
        $this->assertArrayHasKey('comment', $commentVerif);
        $this->assertEquals($comment2->getComment(), $commentVerif['comment']);

        $this->createComment(customData: ['status' => Status::MODERATE->value]);
        $this->createComment(customData: ['status' => Status::VALIDATE->value]);

        $this->client->request(
            'GET',
            $this->router->generate('admin_comment_moderate_comments_filter', ['status' => Status::MODERATE->value]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('nb', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertCount(1, $content['data']);
    }

    /**
     * Test méthode see()
     * @return void
     */
    public function testSee(): void
    {
        $comment = $this->createComment();

        $this->checkNoAccess('admin_comment_see', ['id' => $comment->getId()]);

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_comment_see', ['id' => $comment->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('comment.see.page_title', domain: 'comment'));
    }

    /**
     * Test méthode getComment()
     * @return void
     */
    public function testGetComment(): void
    {
        $comment = $this->createComment();
        $this->checkNoAccess('admin_comment_load', ['id' => $comment->getId()]);

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_comment_load', ['id' => $comment->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('comment', $content);
        $this->assertArrayHasKey('id', $content['comment']);
        $this->assertEquals($comment->getId(), $content['comment']['id']);
        $this->assertArrayHasKey('author', $content['comment']);
        $this->assertEquals($comment->getAuthor(), $content['comment']['author']);
        $this->assertArrayHasKey('email', $content['comment']);
        $this->assertEquals($comment->getEmail(), $content['comment']['email']);
        $this->assertArrayHasKey('comment', $content['comment']);
        $this->assertEquals($comment->getComment(), $content['comment']['comment']);
        $this->assertArrayHasKey('status', $content['comment']);
        $this->assertEquals($comment->getStatus(), $content['comment']['status']);
        $this->assertArrayHasKey('ip', $content['comment']);
        $this->assertEquals($comment->getIp(), $content['comment']['ip']);
        $this->assertArrayHasKey('userAgent', $content['comment']);
        $this->assertEquals($comment->getUserAgent(), $content['comment']['userAgent']);
        $this->assertArrayHasKey('createdAt', $content['comment']);
        $this->assertArrayHasKey('updateAt', $content['comment']);
        $this->assertArrayHasKey('disabled', $content['comment']);
        $this->assertArrayHasKey('moderationComment', $content['comment']);
        $this->assertEquals($comment->getModerationComment(), $content['comment']['moderationComment']);
        $this->assertArrayHasKey('page', $content['comment']);
        $this->assertIsArray($content['comment']['page']);
        $this->assertArrayHasKey('title', $content['comment']['page']);
        $this->assertArrayHasKey('url', $content['comment']['page']);
        $this->assertArrayHasKey('statusStr', $content['comment']);
    }

    /**
     * Test méthode updateComment()
     * @return void
     */
    public function testUpdateComment(): void
    {
        $comment = $this->createComment();
        $data = [
            'comment' => [
                'id' => $comment->getId(),
                'author' => 'John Doe',
                'email' => 'john-doe@monemail.com',
                'comment' => 'Je suis un commentaire **en attente de validation**',
                'status' => Status::VALIDATE->value,
                'ip' => '1.1.1',
                'userAgent' => 'windows',
                'createdAt' => '16/04/25 13 37',
                'updateAt' => '01/05/25 08 37',
                'disabled' => false,
                'moderationComment' => null,
                'page' => [
                    'title' => 'Bienvenue sur NatheoCMS',
                    'url' => '/admin/fr/page/update/6',
                ],
                'statusStr' => "<span class=\"badge text-bg-success\">Validé</span>",
            ],
        ];

        $this->checkNoAccess('admin_comment_save', methode: 'PUT');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_comment_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $commentRepo = $this->em->getRepository(Comment::class);
        $comment = $commentRepo->find($comment->getId());
        $this->assertEquals(Status::VALIDATE->value, $comment->getStatus());
        $this->assertEquals('Je suis un commentaire **en attente de validation**', $comment->getComment());
    }

    /**
     * Test méthode updateCommentModerate()
     * @return void
     */
    public function testUpdateCommentModerate(): void
    {
        $comment1 = $this->createComment();
        $comment2 = $this->createComment();
        $comment3 = $this->createComment();

        $data = [
            'selected' => [$comment1->getId(), $comment2->getId(), $comment3->getId()],
            'status' => Status::MODERATE->value,
            'moderateComment' => 'Commentaire modéré en test unitaire',
        ];

        $this->checkNoAccess('admin_comment_update_moderate_comment', methode: 'POST');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'POST',
            $this->router->generate('admin_comment_update_moderate_comment'),
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $commentRepo = $this->em->getRepository(Comment::class);
        $comments = $commentRepo->findAll();

        foreach ($comments as $comment) {
            $this->assertEquals(Status::MODERATE->value, $comment->getStatus());
            $this->assertEquals('Commentaire modéré en test unitaire', $comment->getModerationComment());
        }
    }
}
