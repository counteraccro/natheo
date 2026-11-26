<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modification d'un commentaire (texte, statut, raison de la modération), un champ absent reste inchangé
 */

namespace App\Api\V2\Input\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use App\Api\V2\Metadata\EnumSlugPropertyMetadataFactory;
use App\Api\V2\Validator\Admin\Content\ValidCommentInput;
use App\Enum\Admin\Comment\CommentStatus;
use Symfony\Component\Validator\Constraints as Assert;

#[ValidCommentInput]
final class UpdateCommentInput
{
    /**
     * Texte en Markdown
     */
    public ?string $comment = null;

    #[ApiProperty(extraProperties: [EnumSlugPropertyMetadataFactory::EXTRA_PROPERTY => CommentStatus::class])]
    #[Assert\Choice(callback: [CommentStatus::class, 'getSlugs'])]
    public ?string $status = null;

    /**
     * Raison de la modération, gardée seulement avec le statut moderate
     */
    public ?string $moderationComment = null;

    /**
     * Vrai quand seul le statut est modifié
     * @return bool
     */
    public function isStatusOnly(): bool
    {
        return $this->status !== null && $this->comment === null && $this->moderationComment === null;
    }
}
