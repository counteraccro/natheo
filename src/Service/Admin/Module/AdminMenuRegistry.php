<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Registre des entrées du menu du back-office, déclarées par les modules et par les services marqués AsAdminMenu
 */

namespace App\Service\Admin\Module;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class AdminMenuRegistry
{
    /**
     * Entrées indexées par identifiant, construites au premier appel
     * @var array<string, AdminMenuItem>|null
     */
    private ?array $items = null;

    /**
     * Identifiant du module de chaque entrée, null pour une entrée sans module
     * @var array<string, string|null>
     */
    private array $moduleIds = [];

    /**
     * Entrées triées, indexées par identifiant du parent ('' pour le premier niveau)
     * @var array<string, AdminMenuItem[]>
     */
    private array $children = [];

    /**
     * @param AdminModuleRegistry $moduleRegistry
     * @param iterable<AdminMenuProviderInterface> $menus
     */
    public function __construct(
        private readonly AdminModuleRegistry $moduleRegistry,
        #[AutowireIterator(AsAdminMenu::TAG)] private readonly iterable $menus,
    ) {}

    /**
     * @return array<string, AdminMenuItem>
     * @throws \LogicException si une entrée est en double, a un parent inconnu ou forme un cycle
     */
    public function all(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $items = [];
        foreach ($this->moduleRegistry->all() as $moduleId => $definition) {
            foreach ($this->moduleRegistry->getService($moduleId)?->getMenuItems() ?? [] as $item) {
                $this->add($items, $item, $moduleId);
            }
        }
        foreach ($this->menus as $menu) {
            foreach ($menu->getMenuItems() as $item) {
                $this->add($items, $item, null);
            }
        }
        $this->check($items);
        $this->children = $this->sortChildren($items);
        return $this->items = $items;
    }

    /**
     * @param string $id
     * @return AdminMenuItem|null
     */
    public function get(string $id): ?AdminMenuItem
    {
        return $this->all()[$id] ?? null;
    }

    /**
     * Entrées enfants triées par position puis par identifiant
     * @param string|null $parent identifiant du parent, null pour le premier niveau
     * @return AdminMenuItem[]
     */
    public function getChildren(?string $parent): array
    {
        $this->all();
        return $this->children[$parent ?? ''] ?? [];
    }

    /**
     * Module qui déclare l'entrée
     * @param string $id
     * @return AdminModule|null
     */
    public function getModule(string $id): ?AdminModule
    {
        $this->all();
        $moduleId = $this->moduleIds[$id] ?? null;
        return $moduleId === null ? null : $this->moduleRegistry->get($moduleId);
    }

    /**
     * Rôles exigés pour voir l'entrée : rôle et module de l'entrée et de ses ancêtres
     * @param string $id
     * @return string[]
     */
    public function getRoles(string $id): array
    {
        $roles = [];
        for (
            $item = $this->get($id);
            $item !== null;
            $item = $item->parent === null ? null : $this->get($item->parent)
        ) {
            $module = $this->getModule($item->id);
            if ($module !== null) {
                $roles[] = $module->role;
            }
            if ($item->role !== null) {
                $roles[] = $item->role;
            }
        }
        return array_values(array_unique($roles));
    }

    /**
     * Entrées dans l'ordre du menu (parcours en profondeur)
     * @return AdminMenuItem[]
     */
    public function flatten(): array
    {
        $result = [];
        $walk = function (?string $parent) use (&$walk, &$result): void {
            foreach ($this->getChildren($parent) as $item) {
                $result[] = $item;
                $walk($item->id);
            }
        };
        $walk(null);
        return $result;
    }

    /**
     * @param array<string, AdminMenuItem> $items
     * @param AdminMenuItem $item
     * @param string|null $moduleId
     * @return void
     */
    private function add(array &$items, AdminMenuItem $item, ?string $moduleId): void
    {
        if (isset($items[$item->id])) {
            throw new \LogicException(sprintf('Entrée de menu du back-office « %s » déclarée deux fois', $item->id));
        }
        $items[$item->id] = $item;
        $this->moduleIds[$item->id] = $moduleId;
    }

    /**
     * Vérifie que chaque parent existe et qu'aucune entrée n'est sa propre ancêtre
     * @param array<string, AdminMenuItem> $items
     * @return void
     */
    private function check(array $items): void
    {
        foreach ($items as $item) {
            $visited = [$item->id => true];
            for ($parent = $item->parent; $parent !== null; $parent = $items[$parent]->parent) {
                if (!isset($items[$parent])) {
                    throw new \LogicException(
                        sprintf('Parent « %s » inconnu pour l\'entrée de menu « %s »', $parent, $item->id),
                    );
                }
                if (isset($visited[$parent])) {
                    throw new \LogicException(
                        sprintf('Cycle dans le menu du back-office à l\'entrée « %s »', $item->id),
                    );
                }
                $visited[$parent] = true;
            }
        }
    }

    /**
     * @param array<string, AdminMenuItem> $items
     * @return array<string, AdminMenuItem[]>
     */
    private function sortChildren(array $items): array
    {
        $children = [];
        foreach ($items as $item) {
            $children[$item->parent ?? ''][] = $item;
        }
        foreach ($children as &$list) {
            usort($list, fn(AdminMenuItem $a, AdminMenuItem $b) => [$a->position, $a->id] <=> [$b->position, $b->id]);
        }
        return $children;
    }
}
