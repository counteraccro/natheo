<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Test du registre des entrées du menu du back-office
 */

namespace App\Tests\Service\Admin\Module;

use App\Service\Admin\Module\AdminMenuRegistry;
use App\Service\Admin\Module\AdminModuleRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdminMenuRegistryTest extends TestCase
{
    use AdminModuleTestTrait;

    /**
     * Entrées rattachées à leur module, triées par position puis par identifiant à chaque niveau
     * @return void
     */
    public function testTree(): void
    {
        $registry = $this->createRegistry(
            [
                $this->createTestModule(
                    'page',
                    items: [
                        $this->createTestMenuItem('page', 'content', 20),
                        $this->createTestMenuItem('page_new', 'page', 20, 'admin_page_add'),
                        $this->createTestMenuItem('page_index', 'page', 10, 'admin_page_index'),
                    ],
                ),
                $this->createTestModule(
                    'tag',
                    items: [$this->createTestMenuItem('tag', 'content', 20, 'admin_tag_index')],
                ),
            ],
            [
                $this->createTestMenu([
                    $this->createTestMenuItem('content', null, 10),
                    $this->createTestMenuItem('home', null, 5, 'admin_dashboard_index'),
                ]),
            ],
        );

        $this->assertSame(['home', 'content'], $this->ids($registry->getChildren(null)));
        $this->assertSame(['page', 'tag'], $this->ids($registry->getChildren('content')));
        $this->assertSame(['page_index', 'page_new'], $this->ids($registry->getChildren('page')));
        $this->assertSame(
            ['home', 'content', 'page', 'page_index', 'page_new', 'tag'],
            $this->ids($registry->flatten()),
        );
        $this->assertSame('page', $registry->getModule('page_new')?->id);
        $this->assertNull($registry->getModule('content'));
        $this->assertTrue($registry->get('content')->isFolder());
        $this->assertFalse($registry->get('tag')->isFolder());
    }

    /**
     * Les rôles d'une entrée cumulent ceux de son module, le sien et ceux de ses ancêtres
     * @return void
     */
    public function testRoles(): void
    {
        $registry = $this->createRegistry(
            [
                $this->createTestModule('token', 'ROLE_ADMIN', [
                    $this->createTestMenuItem('token', 'system'),
                    $this->createTestMenuItem('token_new', 'token', 10, 'admin_api_token_add', [
                        'role' => 'ROLE_SUPER_ADMIN',
                    ]),
                ]),
                $this->createTestModule('extra', 'ROLE_USER', [
                    $this->createTestMenuItem('extra', 'token', 20, 'extra'),
                ]),
            ],
            [$this->createTestMenu([$this->createTestMenuItem('system')])],
        );

        $this->assertSame([], $registry->getRoles('system'));
        $this->assertSame(['ROLE_ADMIN'], $registry->getRoles('token'));
        $this->assertSame(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], $registry->getRoles('token_new'));
        $this->assertSame(['ROLE_USER', 'ROLE_ADMIN'], $registry->getRoles('extra'));
    }

    /**
     * Une entrée déclarée deux fois provoque une erreur
     * @return void
     */
    public function testDuplicateItem(): void
    {
        $registry = $this->createRegistry(
            [$this->createTestModule('page', items: [$this->createTestMenuItem('page')])],
            [$this->createTestMenu([$this->createTestMenuItem('page')])],
        );

        $this->expectExceptionMessage('« page » déclarée deux fois');
        $registry->all();
    }

    /**
     * Une entrée dont le parent n'existe pas provoque une erreur
     * @return void
     */
    public function testUnknownParent(): void
    {
        $registry = $this->createRegistry([], [$this->createTestMenu([$this->createTestMenuItem('page', 'content')])]);

        $this->expectExceptionMessage('Parent « content » inconnu pour l\'entrée de menu « page »');
        $registry->all();
    }

    /**
     * Une entrée qui est sa propre ancêtre provoque une erreur
     * @return void
     */
    public function testCycle(): void
    {
        $registry = $this->createRegistry(
            [],
            [$this->createTestMenu([$this->createTestMenuItem('a', 'b'), $this->createTestMenuItem('b', 'a')])],
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cycle');
        $registry->all();
    }

    /**
     * Une entrée de l'application a sa route et son chemin, et n'est pas un dossier
     * @return void
     */
    public function testAppRouteItem(): void
    {
        $registry = $this->createRegistry(
            [
                $this->createTestModule(
                    'tag',
                    items: [
                        $this->createTestMenuItem('tag_new', options: ['route' => 'tag_new', 'path' => 'tags/new']),
                    ],
                ),
            ],
            [],
        );

        $item = $registry->get('tag_new');
        $this->assertNotNull($item);
        $this->assertTrue($item->isAppRoute());
        $this->assertFalse($item->isFolder());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidAppRouteProvider(): array
    {
        return [
            'deux liens' => [
                ['legacyRoute' => 'admin_tag_add', 'route' => 'tag_new', 'path' => 'tags/new'],
                'a une route Twig et une route de l\'application',
            ],
            'route sans chemin' => [['route' => 'tag_new'], 'doit avoir sa route et son chemin'],
            'chemin sans route' => [['path' => 'tags/new'], 'doit avoir sa route et son chemin'],
            'chemin absolu' => [['route' => 'tag_new', 'path' => '/tags/new'], 'a un chemin invalide « /tags/new »'],
            'chemin vide' => [['route' => 'tag_new', 'path' => ''], 'a un chemin invalide «  »'],
            'paramètres Twig' => [
                ['route' => 'tag_new', 'path' => 'tags/new', 'routeParameters' => ['scope' => 'public']],
                'a des paramètres de route Twig',
            ],
        ];
    }

    /**
     * Une entrée de l'application mal déclarée provoque une erreur
     * @param array<string, mixed> $options
     * @param string $message
     * @return void
     */
    #[DataProvider('invalidAppRouteProvider')]
    public function testInvalidAppRoute(array $options, string $message): void
    {
        $registry = $this->createRegistry(
            [$this->createTestModule('tag', items: [$this->createTestMenuItem('tag_new', options: $options)])],
            [],
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('L\'entrée de menu « tag_new » ' . $message);
        $registry->all();
    }

    /**
     * @param array $modules
     * @param array $menus
     * @return AdminMenuRegistry
     */
    private function createRegistry(array $modules, array $menus): AdminMenuRegistry
    {
        return new AdminMenuRegistry(new AdminModuleRegistry($modules), $menus);
    }

    /**
     * @param array $items
     * @return string[]
     */
    private function ids(array $items): array
    {
        return array_map(fn($item) => $item->id, $items);
    }
}
