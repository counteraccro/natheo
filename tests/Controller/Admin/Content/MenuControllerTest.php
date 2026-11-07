<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller menu
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Content\Menu\MenuPosition;
use App\Enum\Admin\Content\Menu\MenuType;
use App\Service\Admin\Content\Menu\MenuService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MenuControllerTest extends AppWebTestCase
{
    /**
     * test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_menu_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('menu.index.title', domain: 'menu'));
    }

    /**
     * Test méthode loadGridData()
     * @return void
     */
    public function testLoadGridData(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createMenuAllDataDefault();
        }

        $this->checkNoAccess('admin_menu_load_grid_data');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_menu_load_grid_data', ['page' => 1, 'limit' => 4]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(5, $content['nb']);
        $this->assertCount(4, $content['data']);
        $this->assertArrayHasKey('nb', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('column', $content);
        $this->assertArrayHasKey('sql', $content);
        $this->assertArrayHasKey('translate', $content);
    }

    /**
     * Test méthode updateDisabled() : propriétaire, autre contributeur, admin, jeton, menu par défaut
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $user = $this->createUserContributeur();
        $menu = $this->createMenu($user, ['disabled' => false]);
        $defaultMenu = $this->createMenu($user, ['disabled' => false, 'defaultMenu' => true]);
        $this->checkNoAccess('admin_menu_update_disabled', ['id' => $menu->getId()], methode: 'PUT');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_menu_update_disabled', ['id' => $menu->getId()]);

        $this->client->request('PUT', $url);
        $this->assertResponseStatusCodeSame(403, 'Action sans jeton refusée');

        $csrf = $this->getGridCsrfToken('update-disabled');
        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertTrue($this->findMenu($menu->getId())->isDisabled());

        // Menu d'un autre contributeur
        $otherMenu = $this->createMenu(customData: ['disabled' => false]);
        $this->client->request(
            'PUT',
            $this->router->generate('admin_menu_update_disabled', ['id' => $otherMenu->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('menu.error.forbidden');

        // Menu par défaut
        $this->client->request(
            'PUT',
            $this->router->generate('admin_menu_update_disabled', ['id' => $defaultMenu->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('menu.error.default.disabled');

        // Un admin gère le menu d'un autre
        $admin = $this->createUserAdmin();
        $this->client->loginUser($admin, 'admin');
        $this->client->request(
            'PUT',
            $this->router->generate('admin_menu_update_disabled', ['id' => $otherMenu->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $this->getGridCsrfToken('update-disabled')],
        );
        $this->assertResponseIsSuccessful();
        $this->assertTrue($this->findMenu($otherMenu->getId())->isDisabled());
    }

    /**
     * Test méthode delete() : suppression, jeton, droits, menu par défaut, menu utilisé par des pages
     * @return void
     */
    public function testDelete(): void
    {
        $user = $this->createUserContributeur();
        $menu = $this->createMenuAllDataDefault($user);
        $defaultMenu = $this->createMenu($user, ['disabled' => false, 'defaultMenu' => true]);
        $usedMenu = $this->createMenu($user, ['disabled' => false]);
        $this->createPageMenu(menu: $usedMenu);
        $this->createPageMenu(menu: $usedMenu);
        $this->checkNoAccess('admin_menu_delete', ['id' => $menu->getId()], methode: 'DELETE');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_menu_delete', ['id' => $menu->getId()]);

        $this->client->request('DELETE', $url);
        $this->assertResponseStatusCodeSame(403, 'Action sans jeton refusée');

        $csrf = $this->getGridCsrfToken('delete');

        // Menu d'un autre contributeur
        $otherMenu = $this->createMenu(customData: ['disabled' => false]);
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_menu_delete', ['id' => $otherMenu->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertNotNull($this->findMenu($otherMenu->getId()));

        // Menu par défaut
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_menu_delete', ['id' => $defaultMenu->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('menu.error.default.delete');

        // Menu utilisé par 2 pages : refus avec le nombre de pages
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_menu_delete', ['id' => $usedMenu->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('menu.error.delete.used', ['nb' => 2]);
        $this->assertNotNull($this->findMenu($usedMenu->getId()));

        // Suppression autorisée
        $this->client->request('DELETE', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertNull($this->findMenu($menu->getId()));
    }

    /**
     * Supprimer un menu lié à une page laisse la page en base et retire seulement la liaison page_menu
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRemoveMenuKeepsPages(): void
    {
        $menu = $this->createMenu();
        $page = $this->createPageMenu(menu: $menu);
        $pageId = $page->getId();
        $menuId = $menu->getId();

        /** @var MenuService $menuService */
        $menuService = $this->container->get(MenuService::class);
        $menuService->remove($menu);
        $this->em->clear();

        $this->assertNull($this->findMenu($menuId));
        $verifPage = $this->em->getRepository(Page::class)->find($pageId);
        $this->assertNotNull($verifPage, 'La page ne doit pas être supprimée avec le menu');
        $this->assertCount(0, $verifPage->getMenus());
    }

    /**
     * Test méthode switchDefaultMenu() : bascule, jeton, menu désactivé refusé
     * @return void
     */
    public function testSwitchDefaultMenu(): void
    {
        $user = $this->createUserContributeur();
        $this->createMenu($user, ['defaultMenu' => true]);
        for ($i = 0; $i < 5; $i++) {
            $menu = $this->createMenu($user, ['defaultMenu' => false, 'disabled' => false]);
        }
        $disabledMenu = $this->createMenu($user, ['defaultMenu' => false, 'disabled' => true]);

        $this->checkNoAccess('admin_menu_switch_default', ['id' => $menu->getId()], methode: 'PUT');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_menu_switch_default', ['id' => $menu->getId()]);

        $this->client->request('PUT', $url);
        $this->assertResponseStatusCodeSame(403, 'Action sans jeton refusée');

        $csrf = $this->getGridCsrfToken('switch-default');
        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);

        $this->em->clear();
        $this->assertTrue($this->findMenu($menu->getId())->isDefaultMenu());
        $this->assertCount(6, $this->em->getRepository(Menu::class)->findBy(['defaultMenu' => false]));

        // Un menu désactivé ne peut pas devenir le menu par défaut
        $this->client->request(
            'PUT',
            $this->router->generate('admin_menu_switch_default', ['id' => $disabledMenu->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('menu.error.switch.default.disabled');
    }

    /**
     * test méthode add() : création, édition de son menu, refus sur le menu d'un autre, admin
     * @return void
     */
    public function testAdd(): void
    {
        $user = $this->createUserContributeur();
        $menu = $this->createMenuAllDataDefault($user);

        $this->checkNoAccess('admin_menu_add');
        $this->checkNoAccess('admin_menu_update', ['id' => $menu->getId()]);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('menu.add.title', domain: 'menu'));

        $this->client->request('GET', $this->router->generate('admin_menu_update', ['id' => $menu->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('menu.update.title', domain: 'menu'));

        $otherMenu = $this->createMenuAllDataDefault();
        $this->client->request('GET', $this->router->generate('admin_menu_update', ['id' => $otherMenu->getId()]));
        $this->assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_update', ['id' => $otherMenu->getId()]));
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test méthode load_menu() : son menu, menu d'un autre refusé
     * @return void
     */
    public function testGetMenuById(): void
    {
        $user = $this->createUserContributeur();
        $menu = $this->createMenuAllDataDefault($user);
        $this->checkNoAccess('admin_menu_load_menu', ['id' => $menu->getId()]);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_menu_load_menu', ['id' => $menu->getId()]));

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('menu', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertSame([], $content['menu']['pageMenu'], 'Plus de valeur magique -1');

        $otherMenu = $this->createMenuAllDataDefault();
        $this->client->request('GET', $this->router->generate('admin_menu_load_menu', ['id' => $otherMenu->getId()]));
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test méthode save() : édition, création, jeton, droits, lien javascript: refusé
     * @return void
     */
    public function testSave(): void
    {
        $user = $this->createUserContributeur();
        $menu = $this->createMenu($user);
        $data = $this->getSaveData($menu->getId());

        $this->checkNoAccess('admin_menu_save_menu', methode: 'POST');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_menu_save_menu');

        $this->client->request('POST', $url, content: json_encode($data));
        $this->assertResponseStatusCodeSame(403, 'Action sans jeton refusée');

        $csrf = $this->getSaveCsrfToken();
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertFalse($content['redirect']);
        $this->assertStringContainsString(strval($menu->getId()), $content['url']);
        $this->assertEquals('Unit-test', $this->findMenu($menu->getId())->getName());

        // Création
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode($this->getSaveData(0)),
        );
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertTrue($content['redirect']);

        // Lien javascript: refusé, y compris dans un enfant
        $badData = $this->getSaveData($menu->getId());
        $badData['menu']['menuElements'][0]['children'] = [$this->getElementData(-2, 'javascript:alert(1)')];
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($badData));
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonMsg('menu.error.external_link');

        // Menu d'un autre contributeur
        $otherMenu = $this->createMenu();
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode($this->getSaveData($otherMenu->getId())),
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('menu.error.forbidden');
    }

    /**
     * Les éléments existants gardent leur id à la sauvegarde ; les nouveaux sont créés, les absents supprimés
     * @return void
     */
    public function testSaveKeepsMenuElementIds(): void
    {
        $user = $this->createUserContributeur();
        $menu = $this->createMenu($user, ['disabled' => false]);
        $kept = $this->createMenuElement($menu);
        $removed = $this->createMenuElement($menu);
        foreach ($this->locales as $locale) {
            $this->createMenuElementTranslation($kept, ['locale' => $locale]);
            $this->createMenuElementTranslation($removed, ['locale' => $locale]);
        }
        $keptId = $kept->getId();
        $removedId = $removed->getId();

        $data = $this->getSaveData($menu->getId());
        $data['menu']['menuElements'] = [
            $this->getElementData($keptId, '/page-modifiee'),
            $this->getElementData(-5, 'https://www.natheo.fr'),
        ];

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'POST',
            $this->router->generate('admin_menu_save_menu'),
            server: ['HTTP_X-CSRF-TOKEN' => $this->getSaveCsrfToken()],
            content: json_encode($data),
        );
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $elements = $this->findMenu($menu->getId())->getMenuElements();
        $this->assertCount(2, $elements);
        $ids = $elements->map(fn(MenuElement $element) => $element->getId())->toArray();
        $this->assertContains($keptId, $ids, "L'élément existant garde son id");
        $this->assertNotContains($removedId, $ids, "L'élément absent des données est supprimé");

        $keptElement = $this->em->getRepository(MenuElement::class)->find($keptId);
        $this->assertCount(3, $keptElement->getMenuElementTranslations(), 'Traductions mises à jour, pas dupliquées');
        $this->assertEquals('/page-modifiee', $keptElement->getMenuElementTranslationByLocale('fr')->getExternalLink());
    }

    /**
     * Retourne les données de sauvegarde d'un menu avec un élément
     * @param int $id
     * @return array
     */
    private function getSaveData(int $id): array
    {
        return [
            'menu' => [
                'id' => $id,
                'name' => 'Unit-test',
                'type' => MenuType::FOOTER_1_ROW_CENTER->value,
                'position' => MenuPosition::POSITION_FOOTER->value,
                'renderOrder' => 1,
                'defaultMenu' => false,
                'disabled' => true,
                'pageMenu' => [],
                'menuElements' => [$this->getElementData(-1, 'http://www.natheo.fr')],
            ],
        ];
    }

    /**
     * Retourne les données d'un élément de menu avec le même lien externe dans chaque locale
     * @param int $id id existant, ou négatif pour un nouvel élément
     * @param string $externalLink
     * @return array
     */
    private function getElementData(int $id, string $externalLink): array
    {
        $translations = [];
        foreach ($this->locales as $locale) {
            $translations[] = [
                'id' => 0,
                'locale' => $locale,
                'textLink' => $locale . '- lien',
                'externalLink' => $externalLink,
                'link' => '',
            ];
        }
        return [
            'id' => $id,
            'columnPosition' => 1,
            'rowPosition' => 1,
            'linkTarget' => '_self',
            'disabled' => false,
            'parent' => '',
            'page' => '',
            'menuElementTranslations' => $translations,
            'children' => [],
        ];
    }

    /**
     * Retourne le jeton CSRF d'une action de la grille dont l'url contient $urlPart
     * @param string $urlPart
     * @return string
     */
    private function getGridCsrfToken(string $urlPart): string
    {
        $this->client->request('GET', $this->router->generate('admin_menu_load_grid_data', ['page' => 1, 'limit' => 50]));
        $content = json_decode($this->client->getResponse()->getContent(), true);
        foreach ($content['data'] as $row) {
            foreach ($row['action'] as $action) {
                if (is_array($action) && str_contains($action['url'] ?? '', $urlPart) && isset($action['csrf'])) {
                    return $action['csrf'];
                }
            }
        }
        $this->fail('Aucun jeton CSRF trouvé dans les actions de la grille pour ' . $urlPart);
    }

    /**
     * Retourne le jeton CSRF de sauvegarde passé au composant Vue Menu
     * @return string
     */
    private function getSaveCsrfToken(): string
    {
        $this->client->request('GET', $this->router->generate('admin_menu_add'));
        $props = $this->client
            ->getCrawler()
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/Content/Menu/Menu"]')
            ->attr('data-symfony--ux-vue--vue-props-value');
        return json_decode($props, true)['csrf_token_save'];
    }

    /**
     * Vérifie le message d'erreur JSON de la dernière réponse
     * @param string $key clé de traduction du domaine menu
     * @param array $params
     * @return void
     */
    private function assertJsonMsg(string $key, array $params = []): void
    {
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals($this->translator->trans($key, $params, 'menu'), $content['msg']);
    }

    /**
     * Relit un menu en base (sans le cache de l'EntityManager du test)
     * @param int $id
     * @return Menu|null
     */
    private function findMenu(int $id): ?Menu
    {
        $this->em->clear();
        return $this->em->getRepository(Menu::class)->find($id);
    }

    /**
     * Test méthode getListParent()
     * @return void
     */
    public function testGetListParent(): void
    {
        $menu = $this->createMenu();
        $menuElement1 = $this->createMenuElement($menu);
        $subMenuElement = $this->createMenuElement($menu, $menuElement1);
        $subMenuElement2 = $this->createMenuElement($menu, $menuElement1);
        $subSubMenuElement = $this->createMenuElement($menu, $subMenuElement);

        $this->checkNoAccess('admin_menu_list_parent_menu_element', [
            'menuId' => $menu->getId(),
            'elementId' => $subSubMenuElement->getId(),
        ]);

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_menu_list_parent_menu_element', [
                'menuId' => $menu->getId(),
                'elementId' => $subSubMenuElement->getId(),
            ]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('listParent', $content);
        $this->assertEquals(0, $content['listParent'][$menuElement1->getId()]['deep']);
        $this->assertEquals(1, $content['listParent'][$subMenuElement->getId()]['deep']);
        $this->assertEquals(1, $content['listParent'][$subMenuElement2->getId()]['deep']);
    }
}
