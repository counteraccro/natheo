<?php

declare(strict_types=1);
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

    /**
     * Nom de la position dans l'API (header, right, footer, left)
     * @return string
     */
    public function getApiName(): string
    {
        return strtolower(self::getStringByPosition($this->value));
    }

    /**
     * Retourne la position correspondant au nom de l'API, null si inconnu
     * @param string|null $apiName
     * @return self|null
     */
    public static function tryFromApiName(?string $apiName): ?self
    {
        foreach (self::cases() as $position) {
            if ($position->getApiName() === $apiName) {
                return $position;
            }
        }
        return null;
    }
}
