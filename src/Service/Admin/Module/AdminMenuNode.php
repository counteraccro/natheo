<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Entrée du menu visible par l'utilisateur courant, avec son libellé traduit, son adresse et son état
 */

namespace App\Service\Admin\Module;

final class AdminMenuNode
{
    /**
     * Entrée active, celle de la page courante
     * @var bool
     */
    public bool $active = false;

    /**
     * Sous-menu ouvert, parce qu'il contient l'entrée active
     * @var bool
     */
    public bool $open = false;

    /**
     * Entrée affichée dans la sidebar, faux pour un raccourci listé hors du menu et ses parents
     * @var bool
     */
    public bool $inMenu = false;

    /**
     * @param AdminMenuItem $item
     * @param string|null $module identifiant du module, null pour un dossier sans module
     * @param string $label libellé traduit
     * @param string|null $url adresse de la page, null pour un dossier
     * @param int|null $badge nombre affiché à côté du libellé
     * @param AdminMenuNode[] $children
     */
    public function __construct(
        public readonly AdminMenuItem $item,
        public readonly ?string $module,
        public readonly string $label,
        public readonly ?string $url,
        public ?int $badge = null,
        public array $children = [],
    ) {}

    /**
     * Indique si l'entrée s'affiche dans la sidebar : non masquée, et lien ou dossier avec une entrée affichée
     * @return bool
     */
    public function isDisplayed(): bool
    {
        if ($this->item->hidden) {
            return false;
        }
        return $this->url !== null || $this->getDisplayedChildren() !== [];
    }

    /**
     * @return AdminMenuNode[]
     */
    public function getDisplayedChildren(): array
    {
        return array_values(array_filter($this->children, fn(AdminMenuNode $child) => $child->isDisplayed()));
    }
}
