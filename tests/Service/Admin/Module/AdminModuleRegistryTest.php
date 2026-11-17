<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du registre des modules du back-office
 */

namespace App\Tests\Service\Admin\Module;

use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AdminModuleRegistry;
use App\Tests\Api\V2\Module\ProbeModule;
use App\Tests\AppWebTestCase;

class AdminModuleRegistryTest extends AppWebTestCase
{
    /**
     * Les modules marqués AsAdminModule sont lus par le registre du conteneur
     * @return void
     */
    public function testContainerModules(): void
    {
        $registry = $this->container->get(AdminModuleRegistry::class);
        $this->assertSame(ProbeModule::ID, $registry->get(ProbeModule::ID)?->id);
        $this->assertNull($registry->get('inconnu'));
    }

    /**
     * Deux modules avec le même identifiant provoquent une erreur
     * @return void
     */
    public function testDuplicateModule(): void
    {
        $module = $this->createModule('tag');
        $registry = new AdminModuleRegistry([$module, $this->createModule('page'), $module]);

        $this->expectException(\LogicException::class);
        $registry->all();
    }

    /**
     * @param string $id
     * @return AdminModuleInterface
     */
    private function createModule(string $id): AdminModuleInterface
    {
        return new class ($id) implements AdminModuleInterface {
            public function __construct(private readonly string $id) {}

            public function getDefinition(): AdminModule
            {
                return new AdminModule($this->id, 'ROLE_CONTRIBUTEUR');
            }
        };
    }
}
