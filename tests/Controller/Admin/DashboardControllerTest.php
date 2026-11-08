<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test DashboardController
 */
namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\Content\Page\PageValidationStatus;
use App\Enum\Admin\DashboardBlock;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

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
        $this->assertSelectorTextContains('h1', $this->translator->trans('dashboard.index.title', domain: 'dashboard'));
        $this->assertThemeInitBeforeStylesheets();
        $siteName = $this->container->get(OptionSystemService::class)->getValueByKey(OptionSystem::OS_SITE_NAME->value);
        $this->assertPageTitleSame(
            $this->translator->trans('dashboard.index.title', domain: 'dashboard') . ' – ' . $siteName,
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
     * Bloc « Pages à valider » : réservé aux administrateurs, affiché seulement si l'option est active
     * @return void
     */
    public function testIndexBlockPagesPendingValidation(): void
    {
        $admin = $this->createUserAdmin();
        $this->client->loginUser($admin, 'admin');
        $this->assertFalse($this->getDashboardProps()['datas']['dashboard_pages_pending_validation']['active']);

        $this->setPageValidation(true);
        $props = $this->getDashboardProps();
        $this->assertTrue($props['datas']['dashboard_pages_pending_validation']['active']);
        $this->assertTrue($props['roles']['isAdmin']);
        $this->assertStringContainsString(
            'filter=pending_validation',
            $props['urls']['dashboard_pages_pending_validation']['url_pages'],
        );

        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $this->assertFalse($this->getDashboardProps()['roles']['isAdmin']);
    }

    /**
     * Test méthode loadDashboardBlock() : bloc « Pages à valider »
     * @return void
     */
    public function testLoadDashboardBlockPagesPendingValidation(): void
    {
        $route = 'admin_dashboard_load_block';
        $params = ['id' => DashboardBlock::PAGES_PENDING_VALIDATION->value];
        $this->setPageValidation(true);
        $this->checkNoAccess($route, $params, user: $this->createUserContributeur());

        $contributeur = $this->createUserContributeur();
        $pageRecent = $this->createPendingPage($contributeur, new \DateTime('-1 hour'));
        $pageOld = $this->createPendingPage($contributeur, new \DateTime('-2 days'));
        $this->createPage($contributeur, [
            'status' => PageStatus::DRAFT->value,
            'validationStatus' => PageValidationStatus::REFUSED->value,
        ]);
        $this->createPage($contributeur, ['status' => PageStatus::DRAFT->value]);

        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate($route, $params));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertSame([$pageOld->getId(), $pageRecent->getId()], array_column($content['body'], 'id'));
        $this->assertSame($contributeur->getLogin(), $content['body'][0]['author']);
        $this->assertSame(
            $this->router->generate('admin_page_update', ['id' => $pageOld->getId()]),
            $content['body'][0]['url'],
        );

        $this->setPageValidation(false);
        $this->client->request('GET', $this->router->generate($route, $params));
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertNull($content['body']);
        $this->assertSame(
            $this->translator->trans('dashboard.pages_pending_validation.error_inactive', domain: 'dashboard'),
            $content['error'],
        );
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

    /**
     * Test méthode pageDemo() : fil d'Ariane traduit
     * La route est redirigée par OverwriteListener (config/cms/overwrite.yaml), le contrôleur est donc appelé directement
     * @return void
     */
    public function testPageDemo(): void
    {
        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));

        $container = static::getContainer();
        $container->get('request_stack')->push($this->client->getRequest());
        $content = $container->get(DashboardController::class)->pageDemo()->getContent();
        $container->get('request_stack')->pop();

        $crawler = new Crawler($content);
        $label = $this->translator->trans('pagedemo.index.title');
        $this->assertStringContainsString($label, $crawler->filter('nav[aria-label="Breadcrumb"]')->text());
        $this->assertStringNotContainsString('pagedemo.index.title', $content);
    }

    /**
     * Active ou désactive l'option « Validation des pages »
     * @param bool $active
     * @return void
     */
    private function setPageValidation(bool $active): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(OptionSystem::OS_PAGE_VALIDATION->value, $active ? '1' : '0');
    }

    /**
     * Crée une page soumise à validation par $user à la date $validationAt
     * @param User $user
     * @param \DateTime $validationAt
     * @return Page
     */
    private function createPendingPage(User $user, \DateTime $validationAt): Page
    {
        $page = $this->createPage($user, [
            'status' => PageStatus::DRAFT->value,
            'validationStatus' => PageValidationStatus::PENDING->value,
            'validationUser' => $user,
            'validationAt' => $validationAt,
        ]);
        $this->createPageTranslation($page, ['locale' => 'fr']);
        return $page;
    }

    /**
     * Retourne les props passées au composant Vue Dashboard
     * @return array
     */
    private function getDashboardProps(): array
    {
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));
        $this->assertResponseIsSuccessful();
        $props = $this->client
            ->getCrawler()
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/Dashboard/Dashboard"]')
            ->attr('data-symfony--ux-vue--vue-props-value');
        return json_decode($props, true);
    }
}
