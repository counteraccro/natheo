<?php
/**
 * Enum des position
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Content\Menu;

enum MenuPosition: int
{
    case POSITION_HEADER = 1;
    case POSITION_RIGHT = 2;
    case POSITION_FOOTER = 3;
    case POSITION_LEFT = 4;

    private const CONF = [
        self::POSITION_HEADER->value => [
            'string' => 'HEADER',
        ],
        self::POSITION_RIGHT->value => [
            'string' => 'RIGHT',
        ],
        self::POSITION_FOOTER->value => [
            'string' => 'FOOTER',
        ],
        self::POSITION_LEFT->value => [
            'string' => 'LEFT',
        ],
    ];

    /**
     * Retourne la string de la position
     * @param int $position
     * @return string
     */
    public static function getStringByPosition(int $position): string
    {
        if (isset(self::CONF[$position])) {
            return self::CONF[$position]['string'];
        }
        return 'NONE';
    }
}
