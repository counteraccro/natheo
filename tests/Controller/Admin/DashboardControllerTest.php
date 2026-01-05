<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test DashboardController
 */
namespace App\Tests\Controller\Admin;

use App\Enum\Admin\DashboardBlock;
use App\Tests\AppWebTestCase;
use App\Utils\Dashboard\DashboardKey;

class DashboardControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index
     * @return void
     */
    public function testIndex(): void
    {
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));
        $this->assertResponseStatusCodeSame(302);

        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            '#unit-test',
            $this->translator->trans('dashboard.index', domain: 'dashboard'),
        );
    }

    /**
     * Test méthode loadDashboardBlock()
     * @return void
     */
    public function testLoadDashboardBlock(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_dashboard_load_block', [
                'id' => DashboardBlock::HELP_FIRST_CONNEXION->value,
            ]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertArrayHasKey('body', $content);
        $this->assertTrue($content['success']);
        $this->assertNotEmpty($content['body']);

        $this->client->request('GET', $this->router->generate('admin_dashboard_load_block', ['id' => 'test']));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertArrayHasKey('body', $content);
        $this->assertArrayHasKey('error', $content);
        $this->assertFalse($content['success']);
        $this->assertEmpty($content['body']);
        $this->assertNotEmpty($content['error']);
    }
}
