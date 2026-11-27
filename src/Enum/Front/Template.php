<?php

declare(strict_types=1);
/**
 * Enum des template de natheoCMS
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Front;

enum Template: string
{
    case NATHEO_HORIZON = 'natheo_horizon';

    case NATHEO_STORM = 'natheo_storm';

    case NATHEO_BASE = 'natheo_base';

    /**
     * Classe CSS du texte des blocs du thème (HTML rendu depuis le Markdown)
     * @return string
     */
    public function getTextClass(): string
    {
        return match ($this) {
            self::NATHEO_HORIZON => 'nh-prose',
            self::NATHEO_STORM => 'sf-prose',
            self::NATHEO_BASE => 'natheo-base-content',
        };
    }

    public static function toArray(): array
    {
        $return = [];
        foreach (self::cases() as $value) {
            $return[] = $value->value;
        }
        return $return;
    }
}
