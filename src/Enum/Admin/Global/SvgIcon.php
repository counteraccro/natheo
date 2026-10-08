<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Icônes SVG (outline 24x24) de l'administration, la valeur correspond au tracé (attribut d)
 */

namespace App\Enum\Admin\Global;

enum SvgIcon: string
{
    /**
     * Cadenas
     */
    case LOCK = 'M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z';

    /**
     * Oeil barré
     */
    case EYE_SLASH = 'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';

    /**
     * Information
     */
    case INFO = 'M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';

    /**
     * Point d'interrogation (aide)
     */
    case QUESTION = 'M9.529 9.988a2.502 2.502 0 1 1 5 .191A2.441 2.441 0 0 1 12 12.582V14m-.01 3.008H12M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';

    /**
     * Cloche
     */
    case BELL = 'M12 5.365V3m0 2.365a5.338 5.338 0 0 1 5.133 5.368v1.8c0 2.386 1.867 2.982 1.867 4.175 0 .593 0 1.193-.538 1.193H5.538c-.538 0-.538-.6-.538-1.193 0-1.193 1.867-1.789 1.867-4.175v-1.8A5.338 5.338 0 0 1 12 5.365ZM8.733 18c.094.852.306 1.54.944 2.112a3.48 3.48 0 0 0 4.646 0c.638-.572 1.236-1.8 1.236-2.112h-6.826Z';

    /**
     * Souris
     */
    case MOUSE = 'M12 3a6 6 0 0 0-6 6v6a6 6 0 0 0 12 0V9a6 6 0 0 0-6-6Zm0 0v5';

    /**
     * Enveloppe (email)
     */
    case MAIL = 'm3.5 5.5 7.893 6.036a1 1 0 0 0 1.214 0L20.5 5.5M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z';

    /**
     * Menu (3 lignes)
     */
    case MENU = 'M5 7h14M5 12h14M5 17h14';

    /**
     * Crayon (édition)
     */
    case PEN = 'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28';

    /**
     * Génère le SVG de l'icône
     * @param string $class classes CSS du SVG
     * @return string
     */
    public function render(string $class = 'w-4 h-4'): string
    {
        return self::renderPath($this->value, $class);
    }

    /**
     * Génère le SVG d'un tracé quelconque (ex : icône stockée en base)
     * @param string|null $path tracé SVG (attribut d)
     * @param string $class classes CSS du SVG
     * @return string
     */
    public static function renderPath(?string $path, string $class = 'w-4 h-4'): string
    {
        return '<svg class="' .
            htmlspecialchars($class, ENT_QUOTES) .
            '" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">' .
            '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' .
            htmlspecialchars((string) $path, ENT_QUOTES) .
            '"/></svg>';
    }
}
