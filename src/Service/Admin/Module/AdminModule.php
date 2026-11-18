<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Définition d'un module du back-office
 */

namespace App\Service\Admin\Module;

final readonly class AdminModule
{
    /**
     * Domaine de traduction des modules du cœur
     * @var string
     */
    public const string DEFAULT_TRANSLATION_DOMAIN = 'admin_module';

    /**
     * @param string $id identifiant, repris par l'extraProperties « module » de ses opérations d'API
     * @param string $role rôle minimal de l'utilisateur pour accéder au module
     * @param string $label clé de traduction du nom du module
     * @param string $description clé de traduction de la description du module
     * @param bool $locked module qui ne peut pas être désactivé
     * @param string[] $routePrefixes préfixes (ou noms complets) des routes Symfony du module
     * @param string[] $routeExceptions routes des préfixes qui n'appartiennent pas au module
     * @param string $translationDomain domaine de traduction du nom et de la description
     */
    public function __construct(
        public string $id,
        public string $role,
        public string $label,
        public string $description,
        public bool $locked = false,
        public array $routePrefixes = [],
        public array $routeExceptions = [],
        public string $translationDomain = self::DEFAULT_TRANSLATION_DOMAIN,
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
