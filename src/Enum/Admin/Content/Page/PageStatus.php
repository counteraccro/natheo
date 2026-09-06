<?php
/**
 * Status des pages
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Page;

enum PageStatus: int
{
    /**
     * Status publié pour une page
     */
    case PUBLISH = 1;

    /**
     * Status draft pour une page
     */
    case DRAFT = 2;

    /**
     * Status archived pour une page
     */
    case ARCHIVED = 3;

    private const CONF = [
        self::PUBLISH->value => [
            'css_class' => 'badge-validated',
        ],
        self::ARCHIVED->value => [
            'css_class' => 'badge-moderated',
        ],
        self::DRAFT->value => [
            'css_class' => 'badge-pending',
        ],
    ];

    public function getClassCss(): string
    {
        return self::CONF[$this->value]['css_class'];
    }
}
