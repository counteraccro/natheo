<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Test du menu construit pour l'utilisateur courant et de son rendu dans la sidebar
 */

namespace App\Tests\Service\Admin\Module;

use App\Entity\Admin\System\User;
use App\Enum\Admin\Global\Breadcrumb;
use App\Service\Admin\Module\AdminMenuBuilder;
use App\Service\Admin\Module\AdminMenuNode;
use App\Service\Admin\Module\AdminMenuRegistry;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AdminModuleRegistry;
use App\Service\Admin\Module\ModuleActivationService;
use App\Tests\AppWebTestCase;
use App\Twig\Extension\Admin\System\SidebarExtension;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Twig\Environment;

class AdminMenuBuilderTest extends AppWebTestCase
{
    use AdminModuleTestTrait;

    /**
     * Une entrée exige le rôle de son module et le sien, un dossier sans entrée visible est retiré
     * @return void
     */
    public function testRoles(): void
    {
        $builder = $this->createBuilder($this->createUserContributeur());

        $this->assertSame(['content'], $this->ids($builder->getTree()));
        $this->assertSame(['page', 'tag'], $this->ids($builder->getTree()[0]->children));
        $this->assertSame(['page_index'], $this->ids($builder->getTree()[0]->children[0]->children));
        $this->assertTrue($builder->isModuleVisible('page'));
        $this->assertFalse($builder->isModuleVisible('token'));
        $this->assertFalse($builder->isModuleVisible('inconnu'));
    }

    /**
     * Arbre sur trois niveaux, rangé par position puis par identifiant, avec une entrée qui a un lien et des enfants
     * @return void
     */
    public function testTreeOrder(): void
    {
        $builder = $this->createBuilder($this->createUserSuperAdmin());

        $this->assertSame(['content', 'system'], $this->ids($builder->getTree()));
        $system = $builder->getTree()[1];
        $this->assertSame(['token'], $this->ids($system->children));
        $this->assertSame(['token_back', 'token_public', 'token_new'], $this->ids($system->children[0]->children));
        $this->assertSame(['page', 'tag'], $this->ids($builder->getTree()[0]->children));
        $tag = $builder->getTree()[0]->children[1];
        $this->assertNotNull($tag->url);
        $this->assertSame(['tag_stats'], $this->ids($tag->children));
        $this->assertSame(
            ['page_new', 'token_new'],
            array_map(fn(AdminMenuNode $node) => $node->item->id, $builder->getShortcuts()),
        );
        $this->assertSame(
            ['page', 'tag', 'token'],
            array_map(fn($module) => $module->id, $builder->getVisibleModules()),
        );
    }

    /**
     * Un module désactivé disparaît du menu, des raccourcis et des modules visibles
     * @return void
     */
    public function testDisabledModule(): void
    {
        $this->container->get(ModuleActivationService::class)->setModuleDisabled('page', true);
        $builder = $this->createBuilder($this->createUserSuperAdmin());

        $this->assertSame(['tag'], $this->ids($builder->getTree()[0]->children));
        $this->assertSame(
            ['token_new'],
            array_map(fn(AdminMenuNode $node) => $node->item->id, $builder->getShortcuts()),
        );
        $this->assertFalse($builder->isModuleVisible('page'));
        $this->assertSame(['tag', 'token'], array_map(fn($module) => $module->id, $builder->getVisibleModules()));
    }

    /**
     * Une entrée dont l'opération est désactivée disparaît du menu et des raccourcis, son module restant visible
     * @return void
     */
    public function testDisabledOperation(): void
    {
        $probe = fn() => $this->createTestModule(
            'probe',
            items: [
                $this->createTestMenuItem('probe', 'content', 30, 'admin_page_index'),
                $this->createTestMenuItem('probe_new', 'probe', 10, 'admin_page_index', [
                    'shortcut' => true,
                    'operation' => 'admin_probe_post',
                ]),
            ],
        );
        $shortcuts = fn(AdminMenuBuilder $builder) => array_map(
            fn(AdminMenuNode $node) => $node->item->id,
            $builder->getShortcuts(),
        );
        $this->assertContains('probe_new', $shortcuts($this->createBuilder($this->createUserSuperAdmin(), [$probe()])));

        $this->container->get(ModuleActivationService::class)->setOperationDisabled('admin_probe_post', true);
        $builder = $this->createBuilder($this->createUserSuperAdmin(), [$probe()]);

        $this->assertNotContains('probe_new', $shortcuts($builder));
        $this->assertNotContains(
            'probe_new',
            array_map(fn(AdminMenuNode $node) => $node->item->id, $builder->getFlatList()),
        );
        $this->assertTrue($builder->isModuleVisible('probe'));
    }

    /**
     * L'entrée de la page courante est la seule active, ses ancêtres sont ouverts
     * @return void
     */
    public function testActiveByRoute(): void
    {
        $builder = $this->createBuilder($this->createUserSuperAdmin());
        $tree = $builder->getTree();
        $builder->markActive($tree, 'admin_page_add', [], [['admin_page_index', []]]);

        $active = array_filter($builder->getFlatList(), fn(AdminMenuNode $node) => $node->active);
        $this->assertSame(['page_new'], $this->ids($active));
        $open = array_filter($builder->getFlatList(), fn(AdminMenuNode $node) => $node->open);
        $this->assertSame(['content', 'page'], $this->ids($open));
    }

    /**
     * Deux entrées sur la même route se distinguent par leurs paramètres, dans la requête ou dans le fil d'Ariane
     * @return void
     */
    public function testActiveByParameters(): void
    {
        $builder = $this->createBuilder($this->createUserSuperAdmin());
        $builder->markActive($builder->getTree(), 'admin_api_token_index', ['scope' => 'public']);
        $this->assertSame(['token_public'], $this->activeIds($builder));

        $builder->reset();
        $builder->markActive($builder->getTree(), 'admin_api_token_index', []);
        $this->assertSame([], $this->activeIds($builder));

        $builder->reset();
        $builder->markActive(
            $builder->getTree(),
            'admin_api_token_update',
            ['id' => 3],
            [['admin_api_token_index', ['scope' => 'back_office']]],
        );
        $this->assertSame(['token_back'], $this->activeIds($builder));
    }

    /**
     * Sans entrée pour la page, l'entrée active est celle du fil d'Ariane
     * @return void
     */
    public function testActiveByBreadcrumb(): void
    {
        $builder = $this->createBuilder($this->createUserSuperAdmin());
        $builder->markActive($builder->getTree(), 'admin_page_update', ['id' => 1], [['admin_page_index', []]]);

        $this->assertSame(['page_index'], $this->activeIds($builder));
    }

    /**
     * Une entrée masquée n'est pas affichée mais garde son dossier ouvert sur sa page, et reste un raccourci
     * @return void
     */
    public function testHiddenItem(): void
    {
        $builder = $this->createBuilder($this->createUserSuperAdmin());
        $html = $this->renderSidebar($builder, 'admin_api_token_add');

        $this->assertCount(0, $html->filter('a[href="' . $this->router->generate('admin_api_token_add') . '"]'));
        $this->assertCount(1, $html->filter('#submenu-token.open'));
        $this->assertCount(1, $html->filter('#submenu-system.open'));
        $this->assertCount(0, $html->filter('a.active'));
        $this->assertContains('token_new', array_map(fn($node) => $node->item->id, $builder->getShortcuts()));
    }

    /**
     * Liste de l'API : entrées du menu, puis raccourcis hors du menu avec leurs parents, marqués hors menu
     * @return void
     */
    public function testListedNodes(): void
    {
        $builder = $this->createBuilder($this->createUserSuperAdmin(), [
            $this->createTestModule(
                'archive',
                items: [
                    $this->createTestMenuItem('archive', 'content', 30, options: ['hidden' => true]),
                    $this->createTestMenuItem('archive_new', 'archive', 10, 'admin_menu_add', ['shortcut' => true]),
                ],
            ),
        ]);

        $inMenu = [];
        foreach ($builder->getListedNodes() as $node) {
            $inMenu[$node->item->id] = $node->inMenu;
        }
        $this->assertSame(
            [
                'content' => true,
                'page' => true,
                'page_index' => true,
                'page_new' => true,
                'tag' => true,
                'tag_stats' => true,
                'archive' => false,
                'archive_new' => false,
                'system' => true,
                'token' => true,
                'token_back' => true,
                'token_public' => true,
                'token_new' => false,
            ],
            $inMenu,
        );
    }

    /**
     * Rendu de la sidebar : liens, dossier ouvert, entrée avec lien et enfants, badge du module
     * @return void
     */
    public function testRender(): void
    {
        $builder = $this->createBuilder($this->createUserSuperAdmin());
        $html = $this->renderSidebar($builder, 'admin_menu_index');

        $this->assertCount(1, $html->filter('#submenu-content.open'));
        $this->assertCount(0, $html->filter('#submenu-system.open'));
        $this->assertCount(
            1,
            $html->filter(
                '.active > a[aria-current="page"][href="' . $this->router->generate('admin_menu_index') . '"]',
            ),
        );
        $this->assertCount(
            1,
            $html->filter('#submenu-tag a[href="' . $this->router->generate('admin_menu_add') . '"]'),
        );
        $this->assertSame('4', trim($html->filter('#submenu-content span.rounded-full')->text()));
        $this->assertCount(1, $html->filter('span.rounded-full'));
    }

    /**
     * Une entrée de l'application mène à son écran sous /admin/<langue>/, dans l'arbre, le bouton « Nouveau » et la
     * sidebar des pages Twig
     * @return void
     */
    public function testAppRouteItem(): void
    {
        $builder = $this->createBuilder($this->createUserSuperAdmin(), [
            $this->createTestModule(
                'faq',
                items: [
                    $this->createTestMenuItem(
                        'faq',
                        'content',
                        30,
                        options: ['route' => 'faq_index', 'path' => 'faqs'],
                    ),
                    $this->createTestMenuItem(
                        'faq_new',
                        'faq',
                        10,
                        options: [
                            'route' => 'faq_new',
                            'path' => 'faqs/new',
                            'shortcut' => true,
                        ],
                    ),
                ],
            ),
        ]);

        $faq = array_values(
            array_filter($builder->getFlatList(), fn(AdminMenuNode $node) => $node->item->id === 'faq'),
        );
        $this->assertSame('/admin/fr/faqs', $faq[0]->url);
        $this->assertContains(
            '/admin/fr/faqs/new',
            array_map(fn(AdminMenuNode $node) => $node->url, $builder->getShortcuts()),
        );

        $html = $this->renderSidebar($builder, 'admin_menu_index');
        $this->assertCount(1, $html->filter('a[href="/admin/fr/faqs/new"]'));
    }

    /**
     * @param AdminMenuBuilder $builder
     * @param string $route
     * @return Crawler
     */
    private function renderSidebar(AdminMenuBuilder $builder, string $route): Crawler
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $extension = new SidebarExtension($builder, $requestStack);
        $html = $extension->getSidebar($this->container->get(Environment::class), $route, [
            Breadcrumb::BREADCRUMB->value => [],
        ]);
        return new Crawler('<div>' . $html . '</div>');
    }

    /**
     * Menu de test : contenus (pages, tags avec statistiques), système (tokens sur trois niveaux)
     * @param User $user utilisateur connecté
     * @param AdminModuleInterface[] $extraModules modules ajoutés au menu de test
     * @return AdminMenuBuilder
     */
    private function createBuilder(User $user, array $extraModules = []): AdminMenuBuilder
    {
        $this->container
            ->get('security.token_storage')
            ->setToken(new UsernamePasswordToken($user, 'admin', $user->getRoles()));

        $modules = new AdminModuleRegistry([
            $this->createTestModule(
                'page',
                items: [
                    $this->createTestMenuItem('page', 'content', 10),
                    $this->createTestMenuItem('page_index', 'page', 10, 'admin_page_index'),
                    $this->createTestMenuItem('page_new', 'page', 20, 'admin_page_add', [
                        'role' => 'ROLE_ADMIN',
                        'shortcut' => true,
                    ]),
                ],
            ),
            $this->createTestModule(
                'tag',
                items: [
                    $this->createTestMenuItem('tag', 'content', 10, 'admin_menu_index'),
                    $this->createTestMenuItem('tag_stats', 'tag', 10, 'admin_menu_add'),
                ],
                badges: ['tag' => 4, 'page' => 2, 'tag_stats' => 0],
            ),
            $this->createTestModule('token', 'ROLE_SUPER_ADMIN', [
                $this->createTestMenuItem('token', 'system', 10),
                $this->createTestMenuItem('token_public', 'token', 10, 'admin_api_token_index', [
                    'routeParameters' => ['scope' => 'public'],
                ]),
                $this->createTestMenuItem('token_back', 'token', 10, 'admin_api_token_index', [
                    'routeParameters' => ['scope' => 'back_office'],
                ]),
                $this->createTestMenuItem('token_new', 'token', 30, 'admin_api_token_add', [
                    'hidden' => true,
                    'shortcut' => true,
                ]),
            ]),
            ...$extraModules,
        ]);
        $menus = [
            $this->createTestMenu([
                $this->createTestMenuItem('system', null, 20),
                $this->createTestMenuItem('content', null, 10),
                $this->createTestMenuItem('empty', null, 30),
            ]),
        ];

        return new AdminMenuBuilder(
            new AdminMenuRegistry($modules, $menus),
            $modules,
            $this->container->get(Security::class),
            $this->translator,
            $this->router,
            $this->container->get(ModuleActivationService::class),
        );
    }

    /**
     * @param AdminMenuBuilder $builder
     * @return string[]
     */
    private function activeIds(AdminMenuBuilder $builder): array
    {
        return $this->ids(array_filter($builder->getFlatList(), fn(AdminMenuNode $node) => $node->active));
    }

    /**
     * @param AdminMenuNode[] $nodes
     * @return string[]
     */
    private function ids(array $nodes): array
    {
        return array_values(array_map(fn(AdminMenuNode $node) => $node->item->id, $nodes));
    }
}
