<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de PATCH /admin/comments/{id}
 */

namespace App\Tests\Api\V2\Admin\Content\Comment;

use App\Api\V2\Resource\Admin\Content\AdminComment;
use App\Entity\Admin\Content\Comment\Comment;
use App\Enum\Admin\Comment\CommentStatus;

class AdminCommentPatchTest extends AdminCommentTestCase
{
    /**
     * Modérer enregistre l'utilisateur et la raison sans balises, valider les efface ; un changement de statut est
     * tracé sous son nom, avec ou sans raison ni texte, le même statut renvoyé en update
     * @return void
     */
    public function testModerate(): void
    {
        $headers = $this->contributorHeaders($user);
        $comment = $this->createComment($this->createTitledPage('Alpha'), customData: ['author' => 'Camille']);
        $path = '/admin/comments/' . $comment->getId();
        $id = (string) $comment->getId();

        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['status' => 'moderate', 'moderationComment' => '<b>Publicité</b>'],
            headers: $headers,
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminComment::class);
        $content = $this->decode($response);
        $this->assertSame('moderate', $content['status']);
        $this->assertSame('Publicité', $content['moderationComment']);
        $this->assertSame($user->getLogin(), $content['moderator']['login']);

        $content = $this->decode($this->requestV2('PATCH', $path, body: ['status' => 'validate'], headers: $headers));
        $this->assertSame('validate', $content['status']);
        $this->assertNull($content['moderationComment']);
        $this->assertNull($content['moderator']);

        $this->requestV2('PATCH', $path, body: ['status' => 'wait_validation'], headers: $headers);
        $this->requestV2('PATCH', $path, body: ['status' => 'wait_validation', 'comment' => 'Merci'], headers: $headers);
        $this->assertSame(
            [
                ['moderate', $id, 'Camille'],
                ['validate', $id, 'Camille'],
                ['wait_validation', $id, 'Camille'],
                ['update', $id, 'Camille'],
            ],
            $this->logs(),
        );

        $this->em->clear();
        $saved = $this->em->getRepository(Comment::class)->find($comment->getId());
        $this->assertSame(CommentStatus::WAIT_VALIDATION->value, $saved->getStatus());
        $this->assertNull($saved->getUserModeration());
    }

    /**
     * Le texte modifié seul ne change ni le statut ni le modérateur
     * @return void
     */
    public function testEditText(): void
    {
        $headers = $this->contributorHeaders();
        $moderator = $this->createUserContributeur();
        $comment = $this->createComment($this->createTitledPage('Alpha'), $moderator, [
            'status' => CommentStatus::MODERATE->value,
            'moderationComment' => 'Hors sujet',
        ]);

        $content = $this->decode(
            $this->requestV2(
                'PATCH',
                '/admin/comments/' . $comment->getId(),
                body: ['comment' => 'Texte *corrigé*'],
                headers: $headers,
            ),
        );
        $this->assertSame('Texte *corrigé*', $content['comment']);
        $this->assertSame('moderate', $content['status']);
        $this->assertSame('Hors sujet', $content['moderationComment']);
        $this->assertSame($moderator->getLogin(), $content['moderator']['login']);
    }

    /**
     * Texte vide, raison trop longue et statut inconnu donnent 422 par champ ; commentaire inconnu 404
     * @return void
     */
    public function testValidation(): void
    {
        $headers = $this->contributorHeaders();
        $path = '/admin/comments/' . $this->createComment($this->createTitledPage('Alpha'))->getId();

        $content = $this->assertProblem(
            $this->requestV2(
                'PATCH',
                $path,
                body: ['comment' => '  ', 'moderationComment' => str_repeat('a', 1001), 'status' => 'spam'],
                headers: $headers,
            ),
            422,
        );
        $this->assertEqualsCanonicalizing(
            ['comment', 'moderationComment', 'status'],
            array_column($content['violations'], 'propertyPath'),
        );
        $messages = array_column($content['violations'], 'message', 'propertyPath');
        $this->assertSame(
            $this->translator->trans('comment.error.comment_required', domain: 'comment'),
            $messages['comment'],
        );
        $this->assertProblem($this->requestV2('PATCH', '/admin/comments/999999', body: [], headers: $headers), 404);
        $this->assertSame([], $this->logs());
    }

    /**
     * Un If-Match périmé donne 412, l'ETag change à chaque écriture même dans la même seconde
     * @return void
     */
    public function testConcurrency(): void
    {
        $headers = $this->contributorHeaders();
        $path = '/admin/comments/' . $this->createComment($this->createTitledPage('Alpha'))->getId();
        $etag = $this->requestV2('GET', $path, headers: $headers)->headers->get('ETag');

        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['status' => 'validate'],
            headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
        );
        $this->assertEquals(200, $response->getStatusCode());
        $newEtag = $response->headers->get('ETag');
        $this->assertNotSame($etag, $newEtag);

        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                $path,
                body: ['status' => 'moderate'],
                headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
            ),
            412,
        );
        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['status' => 'moderate'],
            headers: ['HTTP_IF_MATCH' => $newEtag, ...$headers],
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Session back-office et rôle contributeur exigés, commentaire inchangé
     * @return void
     */
    public function testAccess(): void
    {
        $comment = $this->createComment($this->createTitledPage('Alpha'));
        $this->assertAccessDenied('PATCH', '/admin/comments/' . $comment->getId(), ['status' => 'validate']);

        $this->em->clear();
        $saved = $this->em->getRepository(Comment::class)->find($comment->getId());
        $this->assertSame(CommentStatus::WAIT_VALIDATION->value, $saved->getStatus());
    }
}
