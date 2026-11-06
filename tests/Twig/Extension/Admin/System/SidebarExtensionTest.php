<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test SidebarExtension : lien actif et sous-menu ouvert
 */
namespace App\Tests\Twig\Extension\Admin\System;

use App\Entity\Admin\System\SidebarElement;
use App\Tests\AppWebTestCase;

class SidebarExtensionTest extends AppWebTestCase
{
    /**
     * Crée un sous-menu dont l'unique lien pointe sur la liste des menus
     * @return SidebarElement le sous-menu parent
     */
    private function createSubmenuMenu(): SidebarElement
    {
        return $this->createSidebarElement([
            'route' => '#',
            'disabled' => false,
            'children' => ['route' => 'admin_menu_index', 'disabled' => false, 'role' => 'ROLE_USER'],
        ]);
    }

    /**
     * Test méthode getSidebar() : sur la page de liste, le lien est actif et le sous-menu ouvert
     * @return void
     */
    public function testGetSidebarCurrentRoute(): void
    {
        $parent = $this->createSubmenuMenu();
        $this->client->loginUser($this->createUserContributeur(), 'admin');

        $this->client->request('GET', $this->router->generate('admin_menu_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#submenu-' . $parent->getId() . '.open');
        $this->assertSelectorExists(
            '#submenu-' . $parent->getId() . ' a.active[href="' . $this->router->generate('admin_menu_index') . '"]',
        );
    }

    /**
     * Test méthode getSidebar() : sur une page fille, le lien de la page parente reste actif et le sous-menu ouvert
     * @return void
     */
    public function testGetSidebarChildPage(): void
    {
        $parent = $this->createSubmenuMenu();
        $this->client->loginUser($this->createUserContributeur(), 'admin');

        $this->client->request('GET', $this->router->generate('admin_menu_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#submenu-' . $parent->getId() . '.open');
        $this->assertSelectorExists(
            '#submenu-' . $parent->getId() . ' a.active[href="' . $this->router->generate('admin_menu_index') . '"]',
        );
    }

    /**
     * Test méthode getSidebar() : une page dont le fil d'Ariane ne pointe sur aucun lien ne sélectionne rien
     * @return void
     */
    public function testGetSidebarNoParent(): void
    {
        $parent = $this->createSubmenuMenu();
        $this->client->loginUser($this->createUserContributeur(), 'admin');

        $this->client->request('GET', $this->router->generate('admin_user_my_account'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('#submenu-' . $parent->getId() . '.open');
        $this->assertSelectorNotExists('#sidebar a.active');
    }
}
