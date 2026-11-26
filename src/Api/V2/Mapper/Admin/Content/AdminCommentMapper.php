<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion d'un commentaire en ressource d'administration
 */

namespace App\Api\V2\Mapper\Admin\Content;

use App\Api\V2\Output\Admin\Content\AdminCommentPage;
use App\Api\V2\Output\Admin\Global\AdminAuthor;
use App\Api\V2\Output\Admin\Global\Permissions;
use App\Api\V2\Resource\Admin\Content\AdminComment;
use App\Entity\Admin\Content\Comment\Comment;
use App\Enum\Admin\Comment\CommentStatus;

final readonly class AdminCommentMapper
{
    /**
     * Ressource complète, lue avec le commentaire seul
     * @param Comment $comment
     * @param AdminCommentPage $page
     * @param Permissions $permissions
     * @return AdminComment
     */
    public function map(Comment $comment, AdminCommentPage $page, Permissions $permissions): AdminComment
    {
        $user = $comment->getUserModeration();
        $moderator = $user === null ? null : new AdminAuthor((int) $user->getId(), (string) $user->getLogin());
        $resource = $this->mapSummary($comment, '', $page, $moderator, $permissions);
        $resource->comment = (string) $comment->getComment();
        $resource->email = $comment->getEmail();
        $resource->ip = (string) $comment->getIp();
        $resource->userAgent = (string) $comment->getUserAgent();
        $resource->moderationComment = $comment->getModerationComment() ?: null;
        return $resource;
    }

    /**
     * Ressource de la liste
     * @param Comment $comment
     * @param string $excerpt
     * @param AdminCommentPage $page
     * @param AdminAuthor|null $moderator
     * @param Permissions $permissions
     * @return AdminComment
     */
    public function mapSummary(
        Comment $comment,
        string $excerpt,
        AdminCommentPage $page,
        ?AdminAuthor $moderator,
        Permissions $permissions,
    ): AdminComment {
        $resource = new AdminComment();
        $resource->id = (int) $comment->getId();
        $resource->excerpt = $excerpt;
        $resource->author = (string) $comment->getAuthor();
        $resource->status = CommentStatus::tryFrom((int) $comment->getStatus())?->getSlug() ?? '';
        $resource->moderator = $moderator;
        $resource->page = $page;
        $resource->createdAt = $comment->getCreatedAt()?->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $comment->getUpdateAt()?->format(\DateTimeInterface::ATOM);
        $resource->permissions = $permissions;
        return $resource;
    }
}
