<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Registre des modules du back-office, alimenté par les services marqués AsAdminModule
 */

namespace App\Service\Admin\Module;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class AdminModuleRegistry
{
    /**
     * Modules indexés par identifiant, construits au premier appel
     * @var array<string, AdminModule>|null
     */
    private ?array $definitions = null;

    /**
     * Services des modules indexés par identifiant
     * @var array<string, AdminModuleInterface>
     */
    private array $services = [];

    /**
     * @param iterable<AdminModuleInterface> $modules
     */
    public function __construct(#[AutowireIterator(AsAdminModule::TAG)] private readonly iterable $modules) {}

    /**
     * Module de l'identifiant, null s'il n'existe pas
     * @param string $id
     * @return AdminModule|null
     */
    public function get(string $id): ?AdminModule
    {
        return $this->all()[$id] ?? null;
    }

    /**
     * Service qui déclare le module
     * @param string $id
     * @return AdminModuleInterface|null
     */
    public function getService(string $id): ?AdminModuleInterface
    {
        $this->all();
        return $this->services[$id] ?? null;
    }

    /**
     * Module auquel appartient la route Symfony, null si elle n'appartient à aucun
     * @param string $route
     * @return AdminModule|null
     */
    public function findByRoute(string $route): ?AdminModule
    {
        foreach ($this->all() as $definition) {
            if ($definition->hasRoute($route)) {
                return $definition;
            }
        }
        return null;
    }

    /**
     * @return array<string, AdminModule>
     * @throws \LogicException si deux modules ont le même identifiant
     */
    public function all(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $definitions = [];
        foreach ($this->modules as $module) {
            $definition = $module->getDefinition();
            if (isset($definitions[$definition->id])) {
                throw new \LogicException(sprintf('Module du back-office « %s » déclaré deux fois', $definition->id));
            }
            $definitions[$definition->id] = $definition;
            $this->services[$definition->id] = $module;
        }
        return $this->definitions = $definitions;
    }
}
