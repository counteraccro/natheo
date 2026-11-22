<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Thèmes du back-office, choisis par chaque utilisateur
 */

namespace App\Enum\Admin;

use App\Enum\Trait\EnumSlugTrait;

enum AdminTemplate: string
{
    use EnumSlugTrait;

    case NATHEO_STUDIO = 'natheo_studio';

    case NATHEO_ATELIER = 'natheo_atelier';

    /**
     * Thème utilisé quand l'utilisateur n'en a pas choisi ou que sa valeur est inconnue
     * @return self
     */
    public static function default(): self
    {
        return self::NATHEO_STUDIO;
    }

    /**
     * Thème correspondant à la valeur, thème par défaut si elle est inconnue
     * @param string|null $value
     * @return self
     */
    public static function fromValueOrDefault(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::default();
    }
}
