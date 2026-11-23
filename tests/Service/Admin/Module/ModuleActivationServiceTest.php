<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test de l'activation des modules du back-office et de leurs opérations d'API
 */

namespace App\Tests\Service\Admin\Module;

use App\Entity\Admin\System\ModuleActivation;
use App\Enum\Admin\Module\ActivationType;
use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\ModuleActivationService;
use App\Tests\Api\V2\Module\ProbeModule;
use App\Tests\AppWebTestCase;

class ModuleActivationServiceTest extends AppWebTestCase
{
    private ModuleActivationService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->container->get(ModuleActivationService::class);
    }

    /**
     * Tout est activé sans ligne en base
     * @return void
     */
    public function testEnabledByDefault(): void
    {
        $this->assertTrue($this->service->isModuleEnabled(ProbeModule::ID));
        $this->assertTrue($this->service->isOperationEnabled('admin_probe_get'));
        $this->assertTrue($this->service->isOperationEnabled('operation_inconnue'));
    }

    /**
     * Désactiver puis réactiver un module, état relu après le vidage du cache
     * @return void
     */
    public function testSetModuleDisabled(): void
    {
        $this->service->setModuleDisabled(ProbeModule::ID, true);
        $this->assertFalse($this->service->isModuleEnabled(ProbeModule::ID));
        $this->assertFalse($this->service->isOperationEnabled('admin_probe_get'));
        $this->assertCount(1, $this->em->getRepository(ModuleActivation::class)->findAll());

        $this->service->setModuleDisabled(ProbeModule::ID, true);
        $this->assertCount(1, $this->em->getRepository(ModuleActivation::class)->findAll());

        $this->service->setModuleDisabled(ProbeModule::ID, false);
        $this->assertTrue($this->service->isModuleEnabled(ProbeModule::ID));
        $this->assertCount(0, $this->em->getRepository(ModuleActivation::class)->findAll());
    }

    /**
     * Une opération désactivée n'emporte pas le reste du module
     * @return void
     */
    public function testSetOperationDisabled(): void
    {
        $this->service->setOperationDisabled('admin_probe_get', true);
        $this->assertTrue($this->service->isModuleEnabled(ProbeModule::ID));
        $this->assertFalse($this->service->isOperationEnabled('admin_probe_get'));
        $this->assertTrue($this->service->isOperationDisabled('admin_probe_get'));

        $this->service->setOperationDisabled('admin_probe_get', false);
        $this->assertTrue($this->service->isOperationEnabled('admin_probe_get'));
    }

    /**
     * L'état est lu depuis le cache, relu une fois celui-ci vidé
     * @return void
     */
    public function testCache(): void
    {
        $this->assertTrue($this->service->isModuleEnabled(ProbeModule::ID));
        $this->persistAndFlush((new ModuleActivation())->setType(ActivationType::MODULE)->setName(ProbeModule::ID));
        $this->service->reset();
        $this->assertTrue($this->service->isModuleEnabled(ProbeModule::ID));

        $this->service->clearCache();
        $this->assertFalse($this->service->isModuleEnabled(ProbeModule::ID));
    }

    /**
     * La gestion des modules, ses opérations et les modules verrouillés ne peuvent pas être désactivés
     * @return void
     */
    public function testLocked(): void
    {
        $this->assertTrue($this->service->isModuleLocked(CoreModuleId::MODULE_ACTIVATION->value));
        $this->assertTrue($this->service->isModuleLocked(CoreModuleId::DASHBOARD->value));
        $this->assertFalse($this->service->isModuleLocked(CoreModuleId::TAG->value));
        $this->assertTrue($this->service->isOperationLocked('admin_module_get_collection'));
        $this->assertTrue($this->service->isOperationLocked('admin_user_option_patch'));
        $this->assertFalse($this->service->isOperationLocked('admin_tag_delete'));

        foreach ([CoreModuleId::MODULE_ACTIVATION->value, CoreModuleId::DASHBOARD->value] as $id) {
            try {
                $this->service->setModuleDisabled($id, true);
                $this->fail(sprintf('Le module « %s » a été désactivé', $id));
            } catch (\LogicException) {
                $this->assertTrue($this->service->isModuleEnabled($id));
            }
        }

        $this->expectException(\LogicException::class);
        $this->service->setOperationDisabled('admin_menu_item_get_collection', true);
    }

    /**
     * Une ligne écrite à la main pour un élément verrouillé est ignorée
     * @return void
     */
    public function testLockedIgnoresRow(): void
    {
        $this->persistAndFlush(
            (new ModuleActivation())->setType(ActivationType::MODULE)->setName(CoreModuleId::MODULE_ACTIVATION->value),
        );
        $this->persistAndFlush(
            (new ModuleActivation())->setType(ActivationType::OPERATION)->setName('admin_module_get_collection'),
        );
        $this->service->clearCache();

        $this->assertTrue($this->service->isModuleEnabled(CoreModuleId::MODULE_ACTIVATION->value));
        $this->assertTrue($this->service->isOperationEnabled('admin_module_get_collection'));
    }

    /**
     * Un élément inconnu est refusé
     * @return void
     */
    public function testUnknown(): void
    {
        $this->expectException(\LogicException::class);
        $this->service->setModuleDisabled('module_inconnu', true);
    }
}
