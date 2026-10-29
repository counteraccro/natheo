<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test DashboardController
 */
namespace App\Tests\Controller\Admin;

use App\Enum\Admin\DashboardBlock;
use App\Tests\AppWebTestCase;

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
        $this->assertStringNotContainsString(
            'Admin/Dashboard/DashboardStatistics',
            $this->client->getResponse()->getContent(),
        );

        $contributeur = $this->createUserContributeur();
        $this->client->loginUser($contributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'Admin/Dashboard/DashboardStatistics',
            $this->client->getResponse()->getContent(),
        );
    }

    /**
     * Test méthode loadDashboardBlock()
     * @return void
     */
    public function testLoadDashboardBlock(): void
    {
        foreach (DashboardBlock::cases() as $block) {
            $this->checkNoAccess('admin_dashboard_load_block', ['id' => $block->value]);
        }
        $this->checkNoAccess(
            'admin_dashboard_load_block',
            ['id' => DashboardBlock::HELP_FIRST_CONNEXION->value],
            user: $this->createUserAdmin(),
        );

        $contributeur = $this->createUserContributeur();
        $this->client->loginUser($contributeur, 'admin');
        foreach (
            [DashboardBlock::LAST_COMMENT, DashboardBlock::LAST_PAGE_CREATE, DashboardBlock::PAGES_MOST_VIEWED]
            as $block
        ) {
            $this->client->request(
                'GET',
                $this->router->generate('admin_dashboard_load_block', ['id' => $block->value]),
            );
            $this->assertResponseIsSuccessful();
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertTrue($content['success']);
        }

        $user = $this->createUserSuperAdmin();
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
        $this->assertArrayHasKey('links', $content);
        $this->assertArrayNotHasKey(0, $content);
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

    /**
     * Test méthode loadStatistics()
     * @return void
     */
    public function testLoadStatistics(): void
    {
        $this->checkNoAccess('admin_dashboard_load_dashboard_stat');

        $this->clearCacheByKey('dashboard_statistics');
        $user = $this->createUserContributeur();
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_load_dashboard_stat'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);

        $this->assertArrayHasKey('nbPage', $content);
        $this->assertArrayHasKey('nbComments', $content);
        $this->assertArrayHasKey('nbWaitComments', $content);
        $this->assertArrayHasKey('nbUsers', $content);
        $this->assertArrayHasKey('nbViews', $content);

        $this->assertEquals(0, $content['nbPage']);
        $this->assertEquals(0, $content['nbComments']);
        $this->assertEquals(0, $content['nbWaitComments']);
        $this->assertEquals(2, $content['nbUsers']);
        $this->assertEquals(0, $content['nbViews']);
    }
}
