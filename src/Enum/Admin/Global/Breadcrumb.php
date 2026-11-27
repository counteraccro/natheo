<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Enum\Admin\Global;

enum Breadcrumb: string
{
    /**
     * Défini le domaine de traduction
     */
    case DOMAIN = 'domain';

    /**
     * Clé du tableau pour construire le fil d'Ariane
     */
    case BREADCRUMB = 'breadcrumb';

    /**
     * Routes d'un fil d'Ariane et leurs paramètres. Un élément vaut un nom de route ou [route, paramètres], '#' pour
     * un élément sans lien
     * @param array $breadcrumb fil d'Ariane construit par le contrôleur
     * @return array<int, array{0: string, 1: array<string, mixed>}>
     */
    public static function getRoutes(array $breadcrumb): array
    {
        $routes = [];
        foreach ($breadcrumb[self::BREADCRUMB->value] ?? [] as $element) {
            $route = self::getRoute($element);
            if ($route !== null) {
                $routes[] = $route;
            }
        }
        return $routes;
    }

    /**
     * Route et paramètres d'un élément du fil d'Ariane, null pour un élément sans lien
     * @param mixed $element
     * @return array{0: string, 1: array<string, mixed>}|null
     */
    public static function getRoute(mixed $element): ?array
    {
        if (is_array($element) && is_string($element[0] ?? null)) {
            return [$element[0], (array) ($element[1] ?? [])];
        }
        if (is_string($element) && $element !== '#' && $element !== '') {
            return [$element, []];
        }
        return null;
    }
}
