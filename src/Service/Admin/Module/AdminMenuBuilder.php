<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Construit le menu du back-office visible par l'utilisateur courant, pour la sidebar, le header et l'API
 */

namespace App\Service\Admin\Module;

use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminMenuBuilder implements ResetInterface
{
    /**
     * Arbre construit au premier appel
     * @var AdminMenuNode[]|null
     */
    private ?array $tree = null;

    public function __construct(
        private readonly AdminMenuRegistry $menuRegistry,
        private readonly AdminModuleRegistry $moduleRegistry,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ModuleActivationService $activationService,
    ) {}

    /**
     * Oublie l'arbre construit, entre deux requêtes
     * @return void
     */
    public function reset(): void
    {
        $this->tree = null;
    }

    /**
     * Entrées visibles en arbre, entrées masquées comprises, dossiers sans entrée visible retirés
     * @return AdminMenuNode[]
     */
    public function getTree(): array
    {
        if ($this->tree !== null) {
            return $this->tree;
        }

        $nodes = $this->buildChildren(null);
        $this->applyBadges($nodes);
        return $this->tree = $nodes;
    }

    /**
     * Entrées visibles dans l'ordre du menu (parcours en profondeur)
     * @return AdminMenuNode[]
     */
    public function getFlatList(): array
    {
        $result = [];
        $walk = function (array $nodes) use (&$walk, &$result): void {
            foreach ($nodes as $node) {
                $result[] = $node;
                $walk($node->children);
            }
        };
        $walk($this->getTree());
        return $result;
    }

    /**
     * Entrées affichées dans l'ordre du menu, plus les raccourcis hors du menu et leurs parents, pour l'API
     * @return AdminMenuNode[]
     */
    public function getListedNodes(): array
    {
        $result = [];
        $walk = function (array $nodes) use (&$walk, &$result): void {
            foreach ($nodes as $node) {
                if ($node->isDisplayed()) {
                    $node->inMenu = true;
                    $result[$node->item->id] = $node;
                    $walk($node->children);
                }
            }
        };
        $walk($this->getTree());

        $byId = [];
        foreach ($this->getFlatList() as $node) {
            $byId[$node->item->id] = $node;
        }
        // Les parents d'un raccourci sont listés pour que son parent soit présent dans la liste
        foreach ($this->getShortcuts() as $node) {
            for ($current = $node; $current !== null && !isset($result[$current->item->id]); ) {
                $result[$current->item->id] = $current;
                $current = $byId[$current->item->parent ?? ''] ?? null;
            }
        }

        $order = array_flip(array_keys($byId));
        uasort($result, fn(AdminMenuNode $a, AdminMenuNode $b) => $order[$a->item->id] <=> $order[$b->item->id]);
        return array_values($result);
    }

    /**
     * Entrées de création visibles marquées shortcut, pour le bouton « Nouveau » du header
     * @return AdminMenuNode[]
     */
    public function getShortcuts(): array
    {
        return array_values(
            array_filter($this->getFlatList(), fn(AdminMenuNode $node) => $node->item->shortcut && $node->url !== null),
        );
    }

    /**
     * Indique si l'utilisateur courant a accès au module, activé
     * @param string $id
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isModuleVisible(string $id): bool
    {
        $module = $this->moduleRegistry->get($id);
        return $module !== null &&
            $this->security->isGranted($module->role) &&
            $this->activationService->isModuleEnabled($id);
    }

    /**
     * Modules visibles, rangés par la position de leur première entrée dans le menu
     * @return AdminModule[]
     * @throws InvalidArgumentException
     */
    public function getVisibleModules(): array
    {
        return array_values(
            array_filter(
                $this->getModulesInMenuOrder(),
                fn(AdminModule $module) => $this->isModuleVisible($module->id),
            ),
        );
    }

    /**
     * Tous les modules, rangés par la position de leur première entrée dans le menu, ceux sans entrée à la fin
     * @return AdminModule[]
     */
    public function getModulesInMenuOrder(): array
    {
        $order = [];
        foreach ($this->menuRegistry->flatten() as $index => $item) {
            $module = $this->menuRegistry->getModule($item->id);
            if ($module !== null && !isset($order[$module->id])) {
                $order[$module->id] = $index;
            }
        }

        $modules = $this->moduleRegistry->all();
        uasort(
            $modules,
            fn(AdminModule $a, AdminModule $b) => [$order[$a->id] ?? PHP_INT_MAX, $a->id] <=> [
                $order[$b->id] ?? PHP_INT_MAX,
                $b->id,
            ],
        );
        return array_values($modules);
    }

    /**
     * Marque l'entrée de la page courante et ouvre ses ancêtres. Sans entrée pour la page, l'entrée active est la
     * dernière citée par le fil d'Ariane
     * @param AdminMenuNode[] $tree
     * @param string $route route courante
     * @param array<string, mixed> $parameters paramètres de la requête courante
     * @param array<int, array{0: string, 1: array<string, mixed>}> $breadcrumb routes du fil d'Ariane et leurs paramètres
     * @return void
     */
    public function markActive(array $tree, string $route, array $parameters, array $breadcrumb = []): void
    {
        $candidates = [[$route, $parameters], ...array_reverse($breadcrumb)];
        foreach ($candidates as [$candidateRoute, $candidateParameters]) {
            $path = $this->findPath($tree, $candidateRoute, $candidateParameters);
            if ($path !== []) {
                $active = array_pop($path);
                $active->active = true;
                foreach ($path as $ancestor) {
                    $ancestor->open = true;
                }
                return;
            }
        }
    }

    /**
     * Chemin vers la première entrée qui mène à la route avec ces paramètres
     * @param AdminMenuNode[] $nodes
     * @param string $route
     * @param array<string, mixed> $parameters
     * @return AdminMenuNode[] ancêtres puis entrée trouvée, vide si aucune
     */
    private function findPath(array $nodes, string $route, array $parameters): array
    {
        foreach ($nodes as $node) {
            if ($this->matches($node->item, $route, $parameters)) {
                return [$node];
            }
            $path = $this->findPath($node->children, $route, $parameters);
            if ($path !== []) {
                return [$node, ...$path];
            }
        }
        return [];
    }

    /**
     * Une entrée correspond quand sa route est la route demandée et que ses paramètres y ont la même valeur
     * @param AdminMenuItem $item
     * @param string $route
     * @param array<string, mixed> $parameters
     * @return bool
     */
    private function matches(AdminMenuItem $item, string $route, array $parameters): bool
    {
        if ($item->legacyRoute !== $route) {
            return false;
        }
        foreach ($item->routeParameters as $name => $value) {
            if (!isset($parameters[$name]) || (string) $parameters[$name] !== (string) $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string|null $parent
     * @return AdminMenuNode[]
     */
    private function buildChildren(?string $parent): array
    {
        $nodes = [];
        foreach ($this->menuRegistry->getChildren($parent) as $item) {
            if (!$this->isItemGranted($item)) {
                continue;
            }
            $children = $this->buildChildren($item->id);
            if ($item->isFolder() && $children === []) {
                continue;
            }
            $nodes[] = new AdminMenuNode(
                item: $item,
                module: $this->menuRegistry->getModule($item->id)?->id,
                label: $item->label->trans($this->translator),
                url: $this->generateUrl($item),
                children: $children,
            );
        }
        return $nodes;
    }

    /**
     * Adresse de la page Twig ou de l'écran de l'application, null pour un dossier
     * @param AdminMenuItem $item
     * @return string|null
     */
    private function generateUrl(AdminMenuItem $item): ?string
    {
        return match (true) {
            $item->isAppRoute() => $this->urlGenerator->generate('admin_app', ['path' => $item->path]),
            $item->isFolder() => null,
            default => $this->urlGenerator->generate($item->legacyRoute, $item->routeParameters),
        };
    }

    /**
     * Rôles de l'entrée accordés, module et opération de l'entrée activés
     * @param AdminMenuItem $item
     * @return bool
     * @throws InvalidArgumentException
     */
    private function isItemGranted(AdminMenuItem $item): bool
    {
        $module = $this->menuRegistry->getModule($item->id);
        if ($module !== null && !$this->activationService->isModuleEnabled($module->id)) {
            return false;
        }
        if ($item->operation !== null && !$this->activationService->isOperationEnabled($item->operation)) {
            return false;
        }
        foreach ($this->menuRegistry->getRoles($item->id) as $role) {
            if (!$this->security->isGranted($role)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Badges des modules qui en déclarent, calculés seulement quand une de leurs entrées est visible
     * @param AdminMenuNode[] $tree
     * @return void
     */
    private function applyBadges(array $tree): void
    {
        $byModule = [];
        $walk = function (array $nodes) use (&$walk, &$byModule): void {
            foreach ($nodes as $node) {
                if ($node->module !== null) {
                    $byModule[$node->module][$node->item->id] = $node;
                }
                $walk($node->children);
            }
        };
        $walk($tree);

        foreach ($byModule as $moduleId => $nodes) {
            $service = $this->moduleRegistry->getService($moduleId);
            if (!($service instanceof AdminMenuBadgeInterface)) {
                continue;
            }
            foreach ($service->getBadges() as $itemId => $count) {
                if (isset($nodes[$itemId]) && $count > 0) {
                    $nodes[$itemId]->badge = $count;
                }
            }
        }
    }
}
