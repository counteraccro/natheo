<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test SidebarExtension : entrées des modules du cœur selon le rôle, lien actif et sous-menus ouverts
 */
namespace App\Tests\Twig\Extension\Admin\System;

use App\Enum\Admin\Comment\CommentStatus;
use App\Tests\AppWebTestCase;

class SidebarExtensionTest extends AppWebTestCase
{
    /**
     * Un contributeur voit le tableau de bord et les contenus, pas le système ni les outils
     * @return void
     */
    public function testContributeur(): void
    {
        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(
            '#sidebar a.active[href="' . $this->router->generate('admin_dashboard_index') . '"]',
        );
        $this->assertSelectorExists('#submenu-content #submenu-page');
        $this->assertSelectorNotExists('#submenu-system');
        $this->assertSelectorNotExists('#submenu-tools');
    }

    /**
     * Un administrateur voit dans « Système » les entrées ouvertes aux administrateurs seulement
     * @return void
     */
    public function testAdmin(): void
    {
        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));

        $this->assertResponseIsSuccessful();
        foreach (
            ['admin_option-system_change', 'admin_api_doc_index', 'admin_mail_index', 'admin_translation_index']
            as $route
        ) {
            $this->assertSelectorExists('#submenu-system a[href="' . $this->router->generate($route) . '"]');
        }
        $this->assertSelectorExists('#submenu-system #submenu-api_token');
        $this->assertSelectorNotExists('#submenu-user');
        $this->assertSelectorNotExists('#submenu-system a[href="' . $this->router->generate('admin_log_index') . '"]');
        $this->assertSelectorNotExists('#submenu-tools');
    }

    /**
     * Sur une page de création, seule son entrée est active, ses dossiers sont ouverts
     * @return void
     */
    public function testCreatePage(): void
    {
        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_add'));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#submenu-content.open #submenu-menu.open');
        $this->assertSelectorExists('#sidebar a.active[href="' . $this->router->generate('admin_menu_add') . '"]');
        $this->assertCount(1, $this->client->getCrawler()->filter('#sidebar a.active'));
    }

    /**
     * Sur une page d'édition, l'entrée de la liste citée par le fil d'Ariane est active
     * @return void
     */
    public function testEditPage(): void
    {
        $user = $this->createUserContributeur();
        $menu = $this->createMenu($user);
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_update', ['id' => $menu->getId()]));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(
            '#submenu-menu.open a.active[href="' . $this->router->generate('admin_menu_index') . '"]',
        );
    }

    /**
     * Le super administrateur voit le sous-menu des tokens sur trois niveaux, l'entrée active suit le type demandé
     * @return void
     */
    public function testApiTokenScope(): void
    {
        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $url = $this->router->generate('admin_api_token_index', ['scope' => 'back_office']);
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#submenu-system.open #submenu-api_token.open a.active[href="' . $url . '"]');
        $this->assertCount(1, $this->client->getCrawler()->filter('#sidebar a.active'));
        $this->assertSelectorExists('#submenu-tools');
    }

    /**
     * Le nombre de commentaires en attente s'affiche sur le dossier et sur la modération
     * @return void
     */
    public function testCommentBadge(): void
    {
        $this->createComment(customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);
        $this->createComment(customData: ['status' => CommentStatus::WAIT_VALIDATION->value]);
        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));

        $this->assertResponseIsSuccessful();
        $badges = $this->client->getCrawler()->filter('#submenu-content span.rounded-full');
        $this->assertSame(['2', '2'], $badges->each(fn($badge) => trim($badge->text())));
    }

    /**
     * Une page sans entrée ni fil d'Ariane vers une entrée ne sélectionne rien
     * @return void
     */
    public function testNoActiveItem(): void
    {
        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_user_my_account'));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('#sidebar a.active');
        $this->assertSelectorNotExists('#sidebar .submenu.open');
    }

    /**
     * Le bouton « Nouveau » liste les raccourcis visibles dans l'ordre du menu, il est masqué sans raccourci
     * @return void
     */
    public function testShortcuts(): void
    {
        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));
        $this->assertResponseIsSuccessful();
        $links = $this->client->getCrawler()->filter('#newContentDropdown a')->each(fn($link) => $link->attr('href'));
        $expected = [
            $this->router->generate('admin_menu_add'),
            $this->router->generate('admin_page_add'),
            $this->router->generate('admin_app', ['path' => 'tags/new']),
            $this->router->generate('admin_app', ['path' => 'faqs/new']),
        ];
        $this->assertSame($expected, $links);

        $user = $this->createUser();
        $this->generateDefaultOptionUser($user);
        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('#newContentButton');
    }
}
