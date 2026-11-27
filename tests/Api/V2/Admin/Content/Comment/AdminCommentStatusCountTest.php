<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de GET /admin/comment_status_counts
 */

namespace App\Tests\Api\V2\Admin\Content\Comment;

use App\Api\V2\Resource\Admin\Content\AdminCommentStatusCount;
use App\Enum\Admin\Comment\CommentStatus;

class AdminCommentStatusCountTest extends AdminCommentTestCase
{
    /**
     * Un élément par statut, dans l'ordre des statuts, nombre à 0 compris
     * @return void
     */
    public function testCollection(): void
    {
        $headers = $this->contributorHeaders();
        $page = $this->createTitledPage('Alpha');
        $this->createComment($page);
        $this->createComment($page);
        $this->createComment($page, customData: ['status' => CommentStatus::MODERATE->value]);

        $response = $this->requestV2('GET', '/admin/comment_status_counts', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminCommentStatusCount::class);
        $content = $this->decode($response);
        $this->assertSame(
            ['wait_validation' => 2, 'validate' => 0, 'moderate' => 1],
            array_column($content['member'], 'count', 'status'),
        );
    }

    /**
     * Un statut seul, statut inconnu en 404
     * @return void
     */
    public function testItem(): void
    {
        $headers = $this->contributorHeaders();
        $this->createComment($this->createTitledPage('Alpha'));

        $response = $this->requestV2('GET', '/admin/comment_status_counts/wait_validation', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminCommentStatusCount::class);
        $this->assertSame(1, $this->decode($response)['count']);
        $this->assertProblem($this->requestV2('GET', '/admin/comment_status_counts/spam', headers: $headers), 404);
    }

    /**
     * Session back-office et rôle contributeur exigés sur la collection et sur un statut
     * @return void
     */
    public function testAccess(): void
    {
        $this->assertAccessDenied('GET', '/admin/comment_status_counts');
        $this->assertAccessDenied('GET', '/admin/comment_status_counts/wait_validation');
    }
}
