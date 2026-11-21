<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Cohérence des modules du back-office avec les routes, les rôles des contrôleurs et les traductions
 */

namespace App\Tests\Service\Admin\Module;

use App\Service\Admin\Module\AdminMenuRegistry;
use App\Service\Admin\Module\AdminModuleRegistry;
use App\Tests\Api\V2\Module\ProbeModule;
use App\Tests\AppWebTestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatorBagInterface;

class CoreModulesTest extends AppWebTestCase
{
    /**
     * Rôles du back-office, du plus faible au plus élevé
     * @var string[]
     */
    private const array ROLES = ['ROLE_USER', 'ROLE_CONTRIBUTEUR', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];

    /**
     * Langues de l'interface
     * @var string[]
     */
    private const array LOCALES = ['fr', 'en', 'es'];

    /**
     * Dossiers des modules de l'application Vue du back-office, surcharges en dernier
     * @var string[]
     */
    private const array APP_MODULES_DIRS = [
        'assets/vue/Components/Admin/Modules',
        'assets/overwrite/vue/Components/Admin/Modules',
    ];

    /**
     * Chaque entrée de menu mène à une route existante
     * @return void
     */
    public function testLegacyRoutesExist(): void
    {
        foreach ($this->getMenuRegistry()->all() as $item) {
            if ($item->legacyRoute !== null) {
                $this->assertNotNull(
                    $this->router->getRouteCollection()->get($item->legacyRoute),
                    sprintf('Route « %s » de l\'entrée « %s » inconnue', $item->legacyRoute, $item->id),
                );
            }
        }
    }

    /**
     * Chaque entrée de l'application mène à une route Vue déclarée par le module.ts de son module, au même chemin
     * @return void
     */
    public function testAppRoutesExist(): void
    {
        $appRoutes = $this->getAppRoutes();
        $this->assertSame('/design-system', $appRoutes['design_system']['design_system'] ?? null);

        $registry = $this->getMenuRegistry();
        foreach ($registry->all() as $item) {
            if (!$item->isAppRoute()) {
                continue;
            }
            $moduleId = $registry->getModule($item->id)?->id;
            $this->assertSame(
                '/' . $item->path,
                $appRoutes[$moduleId][$item->route] ?? null,
                sprintf(
                    'Route Vue « %s » de l\'entrée « %s » absente du module « %s »',
                    $item->route,
                    $item->id,
                    $moduleId,
                ),
            );
        }
    }

    /**
     * Libellés des modules et des entrées traduits dans chaque langue
     * @return void
     */
    public function testLabelsTranslated(): void
    {
        $translator = $this->container->get('translator');
        $this->assertInstanceOf(TranslatorBagInterface::class, $translator);

        $keys = [];
        foreach ($this->getModuleRegistry()->all() as $module) {
            if ($module->id === ProbeModule::ID) {
                continue;
            }
            $keys[] = [$module->label->getMessage(), $module->label->getDomain()];
            $keys[] = [$module->description->getMessage(), $module->description->getDomain()];
        }
        foreach ($this->getMenuRegistry()->all() as $item) {
            $keys[] = [$item->label->getMessage(), $item->label->getDomain()];
        }

        foreach (self::LOCALES as $locale) {
            $catalogue = $translator->getCatalogue($locale);
            foreach ($keys as [$key, $domain]) {
                $this->assertTrue(
                    $catalogue->defines($key, $domain),
                    sprintf('« %s » non traduit en %s', $key, $locale),
                );
            }
        }
    }

    /**
     * Chaque page du menu exige exactement le rôle de son entrée : ni plus ouverte, ni plus fermée
     * @return void
     */
    public function testPageRoleMatchesMenuItem(): void
    {
        $registry = $this->getMenuRegistry();
        foreach ($registry->all() as $item) {
            if ($item->legacyRoute === null) {
                continue;
            }
            $required = $this->getRouteRoles($this->router->getRouteCollection()->get($item->legacyRoute));
            $itemRoles = $registry->getRoles($item->id);
            foreach (self::ROLES as $role) {
                $this->assertSame(
                    $this->grants($role, $itemRoles),
                    $this->grants($role, $required),
                    sprintf(
                        'Page « %s » et entrée « %s » ne concordent pas pour %s',
                        $item->legacyRoute,
                        $item->id,
                        $role,
                    ),
                );
            }
        }
    }

    /**
     * Aucune route d'un module, AJAX comprises, n'est ouverte à un rôle plus faible que le module
     * @return void
     */
    public function testModuleRoutesRequireModuleRole(): void
    {
        $routes = $this->getBackOfficeRoutes();
        foreach ($this->getModuleRegistry()->all() as $module) {
            $count = 0;
            foreach ($routes as $name => $route) {
                if (!$module->hasRoute($name)) {
                    continue;
                }
                $count++;
                $required = $this->getRouteRoles($route);
                foreach (self::ROLES as $role) {
                    if ($this->grants($role, $required)) {
                        $this->assertTrue(
                            $this->grants($role, [$module->role]),
                            sprintf(
                                'Route « %s » ouverte à %s, plus faible que le module « %s »',
                                $name,
                                $role,
                                $module->id,
                            ),
                        );
                    }
                }
            }
            if ($module->routePrefixes !== []) {
                $this->assertGreaterThan(0, $count, sprintf('Aucune route pour le module « %s »', $module->id));
            }
        }
    }

    /**
     * Une route n'appartient qu'à un seul module
     * @return void
     */
    public function testRouteBelongsToOneModule(): void
    {
        foreach (array_keys($this->getBackOfficeRoutes()) as $name) {
            $owners = array_filter($this->getModuleRegistry()->all(), fn($module) => $module->hasRoute($name));
            $this->assertLessThanOrEqual(
                1,
                count($owners),
                sprintf('Route « %s » dans plusieurs modules : %s', $name, implode(', ', array_keys($owners))),
            );
        }
    }

    /**
     * Routes des pages et des appels AJAX du back-office, les seules désignées par les préfixes des modules
     * @return array<string, Route>
     */
    private function getBackOfficeRoutes(): array
    {
        return array_filter(
            $this->router->getRouteCollection()->all(),
            fn(Route $route) => str_starts_with($route->getPath(), '/admin/'),
        );
    }

    /**
     * Rôles exigés par les attributs IsGranted de la classe et de la méthode du contrôleur
     * @param Route $route
     * @return string[]
     */
    private function getRouteRoles(Route $route): array
    {
        [$class, $method] = explode('::', (string) $route->getDefault('_controller'));
        $reflection = new \ReflectionMethod($class, $method);
        $attributes = [
            ...$reflection->getDeclaringClass()->getAttributes(IsGranted::class),
            ...$reflection->getAttributes(IsGranted::class),
        ];

        $roles = [];
        foreach ($attributes as $attribute) {
            $roles[] = (string) $attribute->newInstance()->attribute;
        }
        return $roles;
    }

    /**
     * Indique si un utilisateur du rôle possède tous les rôles demandés
     * @param string $role
     * @param string[] $required
     * @return bool
     */
    private function grants(string $role, array $required): bool
    {
        $reachable = $this->container->get(RoleHierarchyInterface::class)->getReachableRoleNames([$role]);
        return array_diff($required, $reachable) === [];
    }

    /**
     * Routes déclarées par les module.ts de l'application : [id du module => [nom de la route => chemin]]. Un module de
     * surcharge remplace celui du même id
     * @return array<string, array<string, string>>
     */
    private function getAppRoutes(): array
    {
        $routes = [];
        $projectDir = $this->container->getParameter('kernel.project_dir');
        $idPattern = "/\\bid:\\s*'([^']+)'/";
        $routePattern = "/\\{[^{}]*?\\bpath:\\s*'([^']*)'[^{}]*?\\bname:\\s*'([^']+)'/";
        $screenPattern = "/\\b(?:collectionRoute|formRoute)\\(\\s*'([^']*)',\\s*'([^']+)'/";
        foreach (self::APP_MODULES_DIRS as $directory) {
            foreach (glob($projectDir . '/' . $directory . '/*/module.ts') as $file) {
                $source = (string) file_get_contents($file);
                $this->assertSame(1, preg_match($idPattern, $source, $id), sprintf('id absent de %s', $file));
                preg_match_all($routePattern, $source, $matches, PREG_SET_ORDER);
                preg_match_all($screenPattern, $source, $screens, PREG_SET_ORDER);
                $routes[$id[1]] = [];
                foreach ([...$matches, ...$screens] as [, $path, $name]) {
                    $routes[$id[1]][$name] = $path;
                }
            }
        }
        return $routes;
    }

    /**
     * @return AdminMenuRegistry
     */
    private function getMenuRegistry(): AdminMenuRegistry
    {
        return $this->container->get(AdminMenuRegistry::class);
    }

    /**
     * @return AdminModuleRegistry
     */
    private function getModuleRegistry(): AdminModuleRegistry
    {
        return $this->container->get(AdminModuleRegistry::class);
    }
}
