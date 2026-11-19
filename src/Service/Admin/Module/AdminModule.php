<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.1
 * Définition d'un module du back-office
 */

namespace App\Service\Admin\Module;

use Symfony\Component\Translation\TranslatableMessage;

final readonly class AdminModule
{
    /**
     * @param string $id identifiant, repris par l'extraProperties « module » de ses opérations d'API
     * @param string $role rôle minimal de l'utilisateur pour accéder au module
     * @param TranslatableMessage $label nom du module, écrit avec t() pour que translation:extract trouve sa clé
     * @param TranslatableMessage $description description du module, écrite avec t()
     * @param bool $locked module qui ne peut pas être désactivé
     * @param string[] $routePrefixes préfixes (ou noms complets) des routes des pages du back-office du module, chemins
     * en /admin/ (les routes des opérations d'API sont rattachées par leur extraProperties « module »)
     * @param string[] $routeExceptions routes des préfixes qui n'appartiennent pas au module
     */
    public function __construct(
        public string $id,
        public string $role,
        public TranslatableMessage $label,
        public TranslatableMessage $description,
        public bool $locked = false,
        public array $routePrefixes = [],
        public array $routeExceptions = [],
    ) {}

    /**
     * Indique si la route appartient au module
     * @param string $route
     * @return bool
     */
    public function hasRoute(string $route): bool
    {
        if (in_array($route, $this->routeExceptions, true)) {
            return false;
        }
        foreach ($this->routePrefixes as $prefix) {
            if (str_starts_with($route, $prefix)) {
                return true;
            }
        }
        return false;
    }
}
