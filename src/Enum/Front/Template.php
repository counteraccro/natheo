<?php
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

    public static function toArray(): array
    {
        $return = [];
        foreach (self::cases() as $value) {
            $return[] = $value->value;
        }
        return $return;
    }
}
