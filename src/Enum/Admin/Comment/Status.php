<?php
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Comment;

enum Status: int
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
}
