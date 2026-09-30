<?php

declare(strict_types=1);

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet d'afficher une icône de l'enum SvgIcon depuis Twig
 */

namespace App\Twig\Extension\Admin;

use App\Enum\Admin\Global\SvgIcon;
use Twig\Attribute\AsTwigFunction;

class SvgIconExtension
{
    /**
     * Génère le SVG d'une icône à partir du nom de son case, ex : svg_icon('QUESTION', 'inline w-4 h-4')
     * @param string $name nom du case de l'enum SvgIcon
     * @param string $class classes CSS du SVG
     * @return string
     */
    #[AsTwigFunction('svg_icon', isSafe: ['html'])]
    public function svgIcon(string $name, string $class = 'w-4 h-4'): string
    {
        foreach (SvgIcon::cases() as $icon) {
            if ($icon->name === $name) {
                return $icon->render($class);
            }
        }
        throw new \InvalidArgumentException(sprintf('Icône "%s" inconnue dans %s', $name, SvgIcon::class));
    }
}
