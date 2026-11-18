<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test du registre des modules du back-office
 */

namespace App\Tests\Service\Admin\Module;

use App\Service\Admin\Module\AdminModuleRegistry;
use App\Tests\Api\V2\Module\ProbeModule;
use App\Tests\AppWebTestCase;

class AdminModuleRegistryTest extends AppWebTestCase
{
    use AdminModuleTestTrait;

    /**
     * Les modules marqués AsAdminModule sont lus par le registre du conteneur
     * @return void
     */
    public function testContainerModules(): void
    {
        $registry = $this->container->get(AdminModuleRegistry::class);
        $this->assertSame(ProbeModule::ID, $registry->get(ProbeModule::ID)?->id);
        $this->assertInstanceOf(ProbeModule::class, $registry->getService(ProbeModule::ID));
        $this->assertNull($registry->get('inconnu'));
    }

    /**
     * Deux modules avec le même identifiant provoquent une erreur
     * @return void
     */
    public function testDuplicateModule(): void
    {
        $module = $this->createTestModule('tag');
        $registry = new AdminModuleRegistry([$module, $this->createTestModule('page'), $module]);

        $this->expectException(\LogicException::class);
        $registry->all();
    }
}
