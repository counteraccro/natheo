<?php

declare(strict_types=1);
/**
 * Status des commentaires
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Comment;

enum CommentStatus: int
{
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
     * Slug du statut dans l'API (ex : wait_validation)
     * @return string
     */
    public function getSlug(): string
    {
        return strtolower($this->name);
    }

    /**
     * Retourne le statut correspondant au slug, null si inconnu
     * @param string|null $slug
     * @return self|null
     */
    public static function tryFromSlug(?string $slug): ?self
    {
        foreach (self::cases() as $status) {
            if ($status->getSlug() === $slug) {
                return $status;
            }
        }
        return null;
    }

    /**
     * Slugs de tous les statuts
     * @return string[]
     */
    public static function getSlugs(): array
    {
        return array_map(fn(self $status) => $status->getSlug(), self::cases());
    }
}
