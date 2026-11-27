<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.3
 * Entrée du menu du back-office, dossier ou lien vers une page
 */

namespace App\Service\Admin\Module;

use Symfony\Component\Translation\TranslatableMessage;

final readonly class AdminMenuItem
{
    /**
     * @param string $id identifiant unique de l'entrée
     * @param TranslatableMessage $label libellé, écrit avec t() pour que translation:extract trouve sa clé
     * @param string $icon tracé SVG de l'icône (attribut d)
     * @param string|null $parent identifiant de l'entrée parente, null au premier niveau
     * @param int $position ordre d'affichage parmi les entrées du même parent
     * @param string|null $legacyRoute route de la page Twig, null pour un dossier ou une entrée de l'application
     * @param array<string, string|int> $routeParameters paramètres de la route Twig
     * @param string|null $role rôle exigé en plus de celui du module
     * @param bool $shortcut entrée de création reprise dans le bouton « Nouveau » du header
     * @param bool $hidden entrée connue du registre mais absente de la sidebar
     * @param string|null $route nom de la route Vue d'une entrée de l'application, à la place de $legacyRoute
     * @param string|null $path chemin de cette route sous /admin/<langue>/ (tags, tags/new), avec les filtres de sa liste
     *     (comments?moderation=wait_validation), donné avec $route
     * @param string|null $operation opération d'API du module sans laquelle l'entrée est masquée (admin_tag_post)
     */
    public function __construct(
        public string $id,
        public TranslatableMessage $label,
        public string $icon,
        public ?string $parent = null,
        public int $position = 0,
        public ?string $legacyRoute = null,
        public array $routeParameters = [],
        public ?string $role = null,
        public bool $shortcut = false,
        public bool $hidden = false,
        public ?string $route = null,
        public ?string $path = null,
        public ?string $operation = null,
    ) {}

    /**
     * Indique si l'entrée est un dossier, sans lien
     * @return bool
     */
    public function isFolder(): bool
    {
        return $this->legacyRoute === null && $this->route === null;
    }

    /**
     * Indique si l'entrée mène à un écran de l'application Vue
     * @return bool
     */
    public function isAppRoute(): bool
    {
        return $this->route !== null;
    }
}
