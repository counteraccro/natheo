<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des ressources AdminModuleActivation et AdminOperationActivation
 */

namespace App\Tests\Api\V2\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminModuleActivation;
use App\Api\V2\Resource\Admin\System\AdminOperationActivation;
use App\Entity\Admin\System\ActionLog;
use App\Enum\Admin\Module\CoreModuleId;
use App\Tests\Api\V2\AppApiV2TestCase;
use App\Tests\Api\V2\Module\ProbeModule;

class AdminModuleActivationTest extends AppApiV2TestCase
{
    /**
     * Réservé au super administrateur
     * @return void
     */
    public function testAccess(): void
    {
        $admin = $this->adminSessionHeaders($this->createUserAdmin(['disabled' => false, 'anonymous' => false]));

        $this->assertProblem($this->requestV2('GET', '/admin/module_activations', self::HEADER_NONE), 401);
        $this->assertProblem($this->requestV2('GET', '/admin/module_activations', headers: $admin), 403);
        $this->assertProblem(
            $this->requestV2('PATCH', '/admin/module_activations/tag', body: ['disabled' => true], headers: $admin),
            403,
        );
        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                '/admin/operation_activations/admin_tag_delete',
                body: ['disabled' => true],
                headers: $admin,
            ),
            403,
        );
    }

    /**
     * Tous les modules, dans l'ordre du menu, avec leurs opérations et leurs verrous
     * @return void
     */
    public function testCollection(): void
    {
        $response = $this->requestV2('GET', '/admin/module_activations', headers: $this->superAdminHeaders());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminModuleActivation::class);

        $modules = array_column($this->decode($response)['member'], null, 'id');
        $this->assertSame(CoreModuleId::DASHBOARD->value, array_key_first($modules));
        $this->assertTrue($modules[CoreModuleId::MODULE_ACTIVATION->value]['locked']);
        $this->assertTrue($modules[CoreModuleId::DASHBOARD->value]['locked']);
        $this->assertFalse($modules[CoreModuleId::TAG->value]['locked']);
        $this->assertFalse($modules[CoreModuleId::TAG->value]['disabled']);

        $operations = array_column($modules[CoreModuleId::TAG->value]['operations'], null, 'id');
        $delete = $operations['admin_tag_delete'];
        $this->assertSame('/api/v2/admin/operation_activations/admin_tag_delete', $delete['@id']);
        $this->assertSame(
            ['DELETE', '/admin/tags/{id}', 'delete', false, false],
            [$delete['method'], $delete['path'], $delete['kind'], $delete['locked'], $delete['disabled']],
        );
        $this->assertSame('get_collection', $operations['admin_tag_get_collection']['kind']);

        foreach ($modules[CoreModuleId::MODULE_ACTIVATION->value]['operations'] as $operation) {
            $this->assertTrue($operation['locked'], $operation['id']);
        }
        $dashboard = array_column($modules[CoreModuleId::DASHBOARD->value]['operations'], 'locked', 'id');
        $this->assertTrue($dashboard['admin_module_get_collection']);
    }

    /**
     * Désactiver puis réactiver un module, écriture journalisée
     * @return void
     */
    public function testPatchModule(): void
    {
        $headers = $this->superAdminHeaders();
        $path = '/admin/module_activations/' . ProbeModule::ID;

        $this->assertEquals(200, $this->requestV2('GET', $path, headers: $headers)->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminModuleActivation::class);

        $response = $this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($this->decode($response)['disabled']);
        $ids = array_column(
            $this->decode($this->requestV2('GET', '/admin/modules', headers: $headers))['member'],
            'id',
        );
        $this->assertNotContains(ProbeModule::ID, $ids);
        $this->assertProblem($this->requestV2('GET', '/admin/probes', headers: $headers), 404);

        $log = $this->em->getRepository(ActionLog::class)->findOneBy(['resource' => 'AdminModuleActivation']);
        $this->assertSame(
            [CoreModuleId::MODULE_ACTIVATION->value, 'disable', ProbeModule::ID],
            [$log->getModule(), $log->getAction(), $log->getResourceId()],
        );

        $response = $this->requestV2('PATCH', $path, body: ['disabled' => false], headers: $headers);
        $this->assertFalse($this->decode($response)['disabled']);
        $this->assertEquals(200, $this->requestV2('GET', '/admin/probes', headers: $headers)->getStatusCode());
    }

    /**
     * Désactiver une opération, le reste du module restant servi
     * @return void
     */
    public function testPatchOperation(): void
    {
        $headers = $this->superAdminHeaders();
        $path = '/admin/operation_activations/admin_probe_get';

        $this->assertEquals(200, $this->requestV2('GET', $path, headers: $headers)->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminOperationActivation::class);

        $response = $this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($this->decode($response)['disabled']);
        $this->assertProblem($this->requestV2('GET', '/admin/probes/1', headers: $headers), 404);
        $this->assertEquals(200, $this->requestV2('GET', '/admin/probes', headers: $headers)->getStatusCode());

        $module = $this->decode($this->requestV2('GET', '/admin/module_activations/probe', headers: $headers));
        $this->assertTrue(array_column($module['operations'], 'disabled', 'id')['admin_probe_get']);
        $this->assertFalse($module['disabled']);
    }

    /**
     * Les opérations d'un module désactivé le sont aussi ; en réactiver une réactive le module, pas les autres
     * @return void
     */
    public function testOperationOfDisabledModule(): void
    {
        $headers = $this->superAdminHeaders();
        $path = '/admin/module_activations/' . ProbeModule::ID;
        $this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers);

        $module = $this->decode($this->requestV2('GET', $path, headers: $headers));
        $this->assertNotContains(false, array_column($module['operations'], 'disabled'));

        $response = $this->requestV2(
            'PATCH',
            '/admin/operation_activations/admin_probe_get_collection',
            body: ['disabled' => false],
            headers: $headers,
        );
        $this->assertFalse($this->decode($response)['disabled']);

        $module = $this->decode($this->requestV2('GET', $path, headers: $headers));
        $this->assertFalse($module['disabled']);
        $disabled = array_column($module['operations'], 'disabled', 'id');
        $this->assertFalse($disabled['admin_probe_get_collection']);
        $this->assertTrue($disabled['admin_probe_get']);
        $this->assertEquals(200, $this->requestV2('GET', '/admin/probes', headers: $headers)->getStatusCode());
        $this->assertProblem($this->requestV2('GET', '/admin/probes/1', headers: $headers), 404);
    }

    /**
     * Désactiver la dernière opération active désactive le module, qui retrouve toutes ses opérations à sa réactivation
     * @return void
     */
    public function testLastOperationDisablesModule(): void
    {
        $headers = $this->superAdminHeaders();
        $path = '/admin/module_activations/' . ProbeModule::ID;
        $operations = array_column(
            $this->decode($this->requestV2('GET', $path, headers: $headers))['operations'],
            'id',
        );

        foreach ($operations as $index => $name) {
            $this->requestV2(
                'PATCH',
                '/admin/operation_activations/' . $name,
                body: ['disabled' => true],
                headers: $headers,
            );
            $module = $this->decode($this->requestV2('GET', $path, headers: $headers));
            $this->assertSame($index === array_key_last($operations), $module['disabled']);
        }

        $this->requestV2('PATCH', $path, body: ['disabled' => false], headers: $headers);
        $module = $this->decode($this->requestV2('GET', $path, headers: $headers));
        $this->assertNotContains(true, array_column($module['operations'], 'disabled'));
    }

    /**
     * La gestion des modules, les modules et les opérations verrouillés sont refusés en 409
     * @return void
     */
    public function testLocked(): void
    {
        $headers = $this->superAdminHeaders();
        foreach ([CoreModuleId::MODULE_ACTIVATION->value, CoreModuleId::DASHBOARD->value] as $id) {
            $this->assertProblem(
                $this->requestV2(
                    'PATCH',
                    '/admin/module_activations/' . $id,
                    body: ['disabled' => true],
                    headers: $headers,
                ),
                409,
            );
        }
        foreach (
            ['admin_module_activation_patch', 'admin_operation_activation_patch', 'admin_module_get_collection']
            as $name
        ) {
            $this->assertProblem(
                $this->requestV2(
                    'PATCH',
                    '/admin/operation_activations/' . $name,
                    body: ['disabled' => true],
                    headers: $headers,
                ),
                409,
            );
        }
        $this->assertSame([], $this->em->getRepository(ActionLog::class)->findAll());

        $response = $this->requestV2(
            'PATCH',
            '/admin/module_activations/' . CoreModuleId::DASHBOARD->value,
            body: ['disabled' => false],
            headers: $headers,
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Élément inconnu, corps invalide, If-Match périmé
     * @return void
     */
    public function testErrors(): void
    {
        $headers = $this->superAdminHeaders();
        $this->assertProblem($this->requestV2('GET', '/admin/module_activations/inconnu', headers: $headers), 404);
        $this->assertProblem($this->requestV2('GET', '/admin/operation_activations/inconnue', headers: $headers), 404);
        $this->assertProblem(
            $this->requestV2('PATCH', '/admin/module_activations/probe', body: ['disabled' => null], headers: $headers),
            422,
        );

        $path = '/admin/module_activations/probe';
        $etag = $this->requestV2('GET', $path, headers: $headers)->headers->get('ETag');
        $this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers);
        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                $path,
                body: ['disabled' => false],
                headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
            ),
            412,
        );
    }

    /**
     * @return array<string, string>
     */
    private function superAdminHeaders(): array
    {
        return $this->adminSessionHeaders($this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]));
    }
}
