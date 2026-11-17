<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
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
     * @return array<string, AdminModule>
     * @throws \LogicException si deux modules ont le même identifiant
     */
    public function all(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $this->definitions = [];
        foreach ($this->modules as $module) {
            $definition = $module->getDefinition();
            if (isset($this->definitions[$definition->id])) {
                throw new \LogicException(sprintf('Module du back-office « %s » déclaré deux fois', $definition->id));
            }
            $this->definitions[$definition->id] = $definition;
        }
        return $this->definitions;
    }
}
