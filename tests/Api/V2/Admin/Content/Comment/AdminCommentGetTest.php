<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de GET /admin/comments/{id}
 */

namespace App\Tests\Api\V2\Admin\Content\Comment;

use App\Api\V2\Resource\Admin\Content\AdminComment;
use App\Enum\Admin\Comment\CommentStatus;

class AdminCommentGetTest extends AdminCommentTestCase
{
    /**
     * Texte complet, données d'envoi, raison de la modération et adresse de la page sur le site
     * @return void
     */
    public function testGet(): void
    {
        $headers = $this->contributorHeaders($user);
        $page = $this->createTitledPage('Nathéo 2.0');
        $comment = $this->createComment($page, $user, [
            'comment' => '**Bravo**',
            'status' => CommentStatus::MODERATE->value,
            'moderationComment' => 'Hors sujet',
            'ip' => '192.0.2.14',
        ]);

        $response = $this->requestV2('GET', '/admin/comments/' . $comment->getId(), headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminComment::class);
        $this->assertNotNull($response->headers->get('ETag'));
        $content = $this->decode($response);
        $this->assertSame('**Bravo**', $content['comment']);
        $this->assertSame('moderate', $content['status']);
        $this->assertSame('Hors sujet', $content['moderationComment']);
        $this->assertSame($user->getLogin(), $content['moderator']['login']);
        $this->assertSame('192.0.2.14', $content['ip']);
        $this->assertSame($comment->getEmail(), $content['email']);
        $this->assertSame('/fr/blog/nathéo-2.0-fr', rawurldecode((string) $content['page']['url']));
        $this->assertArrayNotHasKey('excerpt', $content);

        $content = $this->decode(
            $this->requestV2('GET', '/admin/comments/' . $comment->getId() . '?locale=en', headers: $headers),
        );
        $this->assertSame('Nathéo 2.0 en', $content['page']['title']);
        $this->assertSame('/en/blog/nathéo-2.0-en', rawurldecode((string) $content['page']['url']));
    }

    /**
     * Commentaire inconnu
     * @return void
     */
    public function testNotFound(): void
    {
        $content = $this->assertProblem(
            $this->requestV2('GET', '/admin/comments/999999', headers: $this->contributorHeaders()),
            404,
        );
        $this->assertSame($this->translator->trans('comment.error.not_found', domain: 'comment'), $content['detail']);
    }

    /**
     * Session back-office et rôle contributeur exigés
     * @return void
     */
    public function testAccess(): void
    {
        $comment = $this->createComment($this->createTitledPage('Alpha'));
        $this->assertAccessDenied('GET', '/admin/comments/' . $comment->getId());
    }
}
