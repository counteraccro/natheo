<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * test controller AdvancedOptions
 */

namespace App\Tests\Controller\Admin\Tools;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdvancedOptionsControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_advanced_options_index');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_advanced_options_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('advanced_options.index.page_title_h1', domain: 'advanced_options'),
        );
    }

    /**
     * Test méthode switchEnv() : GET refusé, POST sans jeton CSRF refusé
     * @return void
     */
    public function testSwitchEnv(): void
    {
        $this->checkNoAccess('admin_advanced_options_switch_env', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->client->request('GET', $this->router->generate('admin_advanced_options_switch_env'));
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);

        $this->assertActionRefusedWithoutCsrf('admin_advanced_options_switch_env');
    }

    /**
     * Test méthode resetData() sans jeton CSRF
     * @return void
     */
    public function testResetData(): void
    {
        $this->checkNoAccess('admin_advanced_options_reset_data', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->assertActionRefusedWithoutCsrf('admin_advanced_options_reset_data');
    }

    /**
     * Test méthode resetDatabase() sans jeton CSRF
     * @return void
     */
    public function testResetDatabase(): void
    {
        $this->checkNoAccess('admin_advanced_options_reset_database', methode: 'POST');

        $userSuperAdm = $this->createUserSuperAdmin();
        $this->client->loginUser($userSuperAdm, 'admin');

        $this->assertActionRefusedWithoutCsrf('admin_advanced_options_reset_database');
    }

    /**
     * Vérifie qu'une action POST est refusée en JSON sans jeton CSRF valide
     * @param string $route
     * @return void
     */
    private function assertActionRefusedWithoutCsrf(string $route): void
    {
        $this->client->request('POST', $this->router->generate($route), server: [
            'HTTP_X-CSRF-TOKEN' => 'jeton-invalide',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertNotEmpty($content['msg']);
    }
}
