<?php

declare(strict_types=1);
/**
 * Status des commentaires
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Comment;

use App\Enum\Trait\EnumSlugTrait;
use Symfony\Component\Translation\TranslatableMessage;

use function Symfony\Component\Translation\t;

enum CommentStatus: int
{
    use EnumSlugTrait;

    /**
     * En attente de validation
     * @type int
     */
    case WAIT_VALIDATION = 1;

    /**
     * Validé
     * @type int
     */
    case VALIDATE = 2;

    /**
     * Modéré
     */
    case MODERATE = 3;

    private const CONF = [
        self::VALIDATE->value => [
            'css_class' => 'badge-validated',
        ],
        self::MODERATE->value => [
            'css_class' => 'badge-moderated',
        ],
        self::WAIT_VALIDATION->value => [
            'css_class' => 'badge-pending',
        ],
    ];

    public function getClassCss(): string
    {
        return self::CONF[$this->value]['css_class'];
    }

    /**
     * Libellé traduisible du statut
     * @return TranslatableMessage
     */
    public function getLabel(): TranslatableMessage
    {
        return match ($this) {
            self::WAIT_VALIDATION => t('comment.state.wait_validation', domain: 'comment'),
            self::VALIDATE => t('comment.state.validate', domain: 'comment'),
            self::MODERATE => t('comment.state.moderate', domain: 'comment'),
        };
    }
}
