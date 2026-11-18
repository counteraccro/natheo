<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test du registre des modules du back-office
 */

namespace App\Tests\Service\Admin\Module;

use App\Service\Admin\Module\AdminModule;
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

    /**
     * Une route appartient au module de son préfixe, sauf si elle est en exception
     * @return void
     */
    public function testFindByRoute(): void
    {
        $user = new AdminModule(
            'user',
            'ROLE_SUPER_ADMIN',
            'user.label',
            'user.description',
            routePrefixes: ['admin_user_'],
            routeExceptions: ['admin_user_my_account'],
        );
        $registry = new AdminModuleRegistry([
            $this->createTestModule('user', definition: $user),
            $this->createTestModule(
                'page',
                definition: new AdminModule('page', 'ROLE_CONTRIBUTEUR', 'l', 'd', routePrefixes: ['admin_page_']),
            ),
        ]);

        $this->assertSame('user', $registry->findByRoute('admin_user_index')?->id);
        $this->assertSame('page', $registry->findByRoute('admin_page_add')?->id);
        $this->assertNull($registry->findByRoute('admin_user_my_account'));
        $this->assertNull($registry->findByRoute('admin_tag_index'));
    }
}
