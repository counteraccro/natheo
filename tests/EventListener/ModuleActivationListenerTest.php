<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test du blocage des opérations d'API et des pages d'un module désactivé du back-office
 */

namespace App\Tests\EventListener;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\ModuleActivationService;
use App\Tests\Api\V2\AppApiV2TestCase;
use App\Tests\Api\V2\Module\ProbeModule;

class ModuleActivationListenerTest extends AppApiV2TestCase
{
    /**
     * Une opération désactivée répond 404 en problem+json, le reste du module reste servi
     * @return void
     */
    public function testOperationDisabled(): void
    {
        $headers = $this->adminSessionHeaders(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
        );
        $this->getActivationService()->setOperationDisabled('admin_probe_get', true);

        $problem = $this->assertProblem($this->requestV2('GET', '/admin/probes/1', headers: $headers), 404);
        $this->assertSame(
            $this->translator->trans('module_activation.error.disabled', domain: 'module_activation'),
            $problem['detail'],
        );
        $this->assertEquals(200, $this->requestV2('GET', '/admin/probes', headers: $headers)->getStatusCode());

        $this->getActivationService()->setOperationDisabled('admin_probe_get', false);
        $this->assertEquals(200, $this->requestV2('GET', '/admin/probes/1', headers: $headers)->getStatusCode());
    }

    /**
     * Un module désactivé ferme toutes ses opérations, y compris à un appelant non connecté
     * @return void
     */
    public function testModuleDisabled(): void
    {
        $headers = $this->adminSessionHeaders(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
        );
        $this->getActivationService()->setModuleDisabled(ProbeModule::ID, true);

        $this->assertProblem($this->requestV2('GET', '/admin/probes', headers: $headers), 404);
        $this->assertProblem($this->requestV2('GET', '/admin/probes/1', headers: $headers), 404);
        $this->assertProblem($this->requestV2('GET', '/admin/probes', self::HEADER_NONE), 404);
        $this->assertEquals(200, $this->requestV2('GET', '/admin/modules', headers: $headers)->getStatusCode());
    }

    /**
     * Les pages Twig et les routes AJAX d'un module désactivé répondent 404
     * @return void
     */
    public function testTwigRouteOfDisabledModule(): void
    {
        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $url = $this->router->generate('admin_tag_search', ['locale' => 'fr', 'search' => 'label']);

        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        $this->getActivationService()->setModuleDisabled(CoreModuleId::TAG->value, true);
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(404);

        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));
        $this->assertResponseIsSuccessful();
    }

    /**
     * @return ModuleActivationService
     */
    private function getActivationService(): ModuleActivationService
    {
        return $this->container->get(ModuleActivationService::class);
    }
}
