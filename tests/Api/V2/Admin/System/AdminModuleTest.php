<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des ressources AdminModule : modules visibles selon le rôle de l'utilisateur de la session
 */

namespace App\Tests\Api\V2\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminModule;
use App\Service\Admin\Module\ModuleActivationService;
use App\Tests\Api\V2\AppApiV2TestCase;
use App\Tests\Api\V2\Module\ProbeModule;

class AdminModuleTest extends AppApiV2TestCase
{
    /**
     * Modules des contenus, rangés dans l'ordre du menu
     * @var string[]
     */
    private const array CONTENT_MODULES = ['dashboard', 'menu', 'page', 'comment', 'tag', 'media', 'faq'];

    /**
     * Refus sans session, avec un token seul et avec une session ouverte par un token public
     * @return void
     */
    public function testAccess(): void
    {
        $user = $this->createUserAdmin(['disabled' => false, 'anonymous' => false]);
        $publicSession = $this->sessionHeaders($user, 'ROLE_ADMIN_API');
        $backOfficeToken = $this->backOfficeTokenHeaders();

        $this->assertProblem($this->requestV2('GET', '/admin/modules', self::HEADER_NONE), 401);
        $this->assertProblem($this->requestV2('GET', '/admin/modules', self::HEADER_ADMIN), 403);
        $this->assertProblem($this->requestV2('GET', '/admin/modules', headers: $backOfficeToken), 403);
        $this->assertProblem($this->requestV2('GET', '/admin/modules', headers: $publicSession), 403);
    }

    /**
     * Modules visibles selon le rôle, du membre au super administrateur
     * @return void
     */
    public function testCollectionByRole(): void
    {
        $adminModules = ['option_system', 'api_token', 'api_doc', 'mail', 'translation'];
        $cases = [
            'createUser' => ['dashboard', 'my_account'],
            'createUserContributeur' => [...self::CONTENT_MODULES, 'my_account', ProbeModule::ID],
            'createUserAdmin' => [...self::CONTENT_MODULES, ...$adminModules, 'my_account', ProbeModule::ID],
        ];
        foreach ($cases as $factory => $expected) {
            $headers = $this->adminSessionHeaders($this->$factory(['disabled' => false, 'anonymous' => false]));
            $response = $this->requestV2('GET', '/admin/modules', headers: $headers);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertMatchesResourceCollectionJsonSchema(AdminModule::class);
            $this->assertSame($expected, array_column($this->decode($response)['member'], 'id'), $factory);
        }

        $superAdmin = $this->adminSessionHeaders(
            $this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]),
        );
        $ids = array_column(
            $this->decode($this->requestV2('GET', '/admin/modules', headers: $superAdmin))['member'],
            'id',
        );
        $this->assertContains('user', $ids);
        $this->assertContains('demo_overwrite', $ids);
        $this->assertSame(ProbeModule::ID, end($ids));
    }

    /**
     * Un module porte son nom traduit, son verrou et ses opérations d'API
     * @return void
     */
    public function testItem(): void
    {
        $headers = $this->adminSessionHeaders(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
        );

        $response = $this->requestV2('GET', '/admin/modules/' . ProbeModule::ID, headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminModule::class);
        $probe = $this->decode($response);
        $this->assertFalse($probe['locked']);
        $this->assertContains('admin_probe_get_collection', $probe['operations']);
        $this->assertContains('admin_probe_delete', $probe['operations']);

        $dashboard = $this->decode($this->requestV2('GET', '/admin/modules/dashboard', headers: $headers));
        $this->assertTrue($dashboard['locked']);
        $this->assertSame(
            $this->translator->trans('admin_module.module.dashboard.label', domain: 'admin_module'),
            $dashboard['label'],
        );
        $this->assertContains('admin_module_get_collection', $dashboard['operations']);
        $this->assertContains('admin_menu_item_get', $dashboard['operations']);

        $this->assertProblem($this->requestV2('GET', '/admin/modules/user', headers: $headers), 404);
        $this->assertProblem($this->requestV2('GET', '/admin/modules/inconnu', headers: $headers), 404);
    }

    /**
     * Un module désactivé est absent de la liste, une opération désactivée de ses opérations
     * @return void
     */
    public function testDisabled(): void
    {
        $headers = $this->adminSessionHeaders(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
        );
        $activationService = $this->container->get(ModuleActivationService::class);

        $activationService->setOperationDisabled('admin_probe_delete', true);
        $probe = $this->decode($this->requestV2('GET', '/admin/modules/' . ProbeModule::ID, headers: $headers));
        $this->assertNotContains('admin_probe_delete', $probe['operations']);
        $this->assertContains('admin_probe_get', $probe['operations']);

        $activationService->setModuleDisabled(ProbeModule::ID, true);
        $ids = array_column(
            $this->decode($this->requestV2('GET', '/admin/modules', headers: $headers))['member'],
            'id',
        );
        $this->assertNotContains(ProbeModule::ID, $ids);
        $this->assertProblem($this->requestV2('GET', '/admin/modules/' . ProbeModule::ID, headers: $headers), 404);
    }
}
