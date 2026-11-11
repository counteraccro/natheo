<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit un commentaire en ressource Comment de l'API v2
 */

namespace App\Api\V2\Mapper\Content;

use App\Api\V2\Resource\Content\Comment;
use App\Api\V2\Resource\Content\Page;
use App\Entity\Admin\Content\Comment\Comment as CommentEntity;
use App\Enum\Admin\Comment\CommentStatus;

final readonly class CommentMapper
{
    /**
     * @param bool $isModerator sans droit de modération, les contenus non validés sont masqués
     */
    public function __construct(private bool $isModerator) {}

    /**
     * @param CommentEntity $entity
     * @return Comment
     */
    public function map(CommentEntity $entity): Comment
    {
        $status = CommentStatus::tryFrom((int) $entity->getStatus());

        $page = new Page();
        $page->id = (int) $entity->getPage()?->getId();

        $comment = new Comment();
        $comment->id = (int) $entity->getId();
        $comment->page = $page;
        $comment->status = $status?->getSlug() ?? '';
        $comment->author = $entity->getAuthor();
        $comment->comment = $entity->getComment();
        $comment->createdAt = $entity->getCreatedAt();
        $comment->updatedAt = $entity->getUpdateAt();

        if ($this->isModerator) {
            $comment->moderationComment = $entity->getModerationComment() ?: null;
        } elseif ($status !== CommentStatus::VALIDATE) {
            $comment->comment = null;
            if ($status === CommentStatus::WAIT_VALIDATION) {
                $comment->author = null;
            }
        }
        return $comment;
    }
}
