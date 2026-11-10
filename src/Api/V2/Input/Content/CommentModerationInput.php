<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de modération d'un commentaire dans l'API v2 (merge-patch : un champ absent reste inchangé)
 */

namespace App\Api\V2\Input\Content;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Admin\Comment\CommentStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class CommentModerationInput
{
    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['wait_validation', 'validate', 'moderate']])]
    #[Assert\Choice(callback: [CommentStatus::class, 'getSlugs'])]
    public ?string $status = null;

    #[Assert\Length(max: 10000)]
    public ?string $moderationComment = null;
}
