<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test controller Page
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Tests\AppWebTestCase;
use App\Service\Admin\Content\Page\PageHistory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class PageControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_page_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.index.title', domain: 'page'));
    }

    /**
     * Test méthode loadGridData() : données échappées, tri et actions selon les droits
     * @return void
     */
    public function testLoadGridData(): void
    {
        $contributeur = $this->createUserContributeur(['login' => '<b>login</b>']);
        $pageOwn = $this->createPage($contributeur, ['disabled' => false, 'status' => PageStatus::PUBLISH->value]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($pageOwn, ['locale' => $locale, 'titre' => '<img src=x onerror=alert(1)>']);
        }
        $pageOther = $this->createPage(customData: ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($pageOther, ['locale' => $locale]);
        }

        // Page modifiée il y a longtemps, pour le tri par date de mise à jour
        $pageOld = $this->createPage($contributeur);
        $this->createPageTranslation($pageOld, ['locale' => 'fr']);
        $this->em->getConnection()->executeStatement('UPDATE page SET update_at = :d WHERE id = :id', [
            'd' => '2000-01-01 00:00:00',
            'id' => $pageOld->getId(),
        ]);

        $this->checkNoAccess('admin_page_load_grid_data');

        $this->client->loginUser($contributeur, 'admin');
        $url = $this->router->generate('admin_page_load_grid_data', ['page' => 1, 'limit' => 10]);
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertStringNotContainsString('<img', $content);
        $this->assertStringNotContainsString('<b>login', $content);

        $rows = [];
        foreach (json_decode($content, true)['data'] as $row) {
            $rows[(int) $row[$this->translator->trans('page.grid.id', domain: 'page')]] = $row;
        }
        $titleCol = $this->translator->trans('page.grid.title', domain: 'page');
        $this->assertEquals('&lt;img src=x onerror=alert(1)&gt;', $rows[$pageOwn->getId()][$titleCol]);
        // Sa page publiée : désactiver, supprimer, éditer, landing page ; celle d'un autre : rien
        $this->assertCount(4, $rows[$pageOwn->getId()]['action']);
        $this->assertCount(0, $rows[$pageOther->getId()]['action']);

        $this->client->request('GET', $url, ['orderField' => 'updateAt', 'order' => 'ASC']);
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true)['data'];
        $this->assertEquals(
            $pageOld->getId(),
            (int) $data[0][$this->translator->trans('page.grid.id', domain: 'page')],
        );
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $user = $this->createUserContributeur();
        $page = $this->createPageWithTranslations($user, ['disabled' => false]);
        $pageOther = $this->createPageWithTranslations(customData: ['disabled' => false]);
        $landingPage = $this->createPageWithTranslations($user, ['disabled' => false, 'landingPage' => true]);

        $this->checkNoAccess('admin_page_update_disabled', ['id' => $page->getId()], 'PUT');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_page_update_disabled', ['id' => $page->getId()]);

        $this->client->request('PUT', $url);
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getGridCsrfToken('update-disabled');
        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertStringContainsString(
            htmlspecialchars($page->getPageTranslationByLocale('fr')->getTitre(), ENT_QUOTES),
            $content['msg'],
        );
        $this->em->clear();
        $this->assertTrue($this->em->getRepository(Page::class)->find($page->getId())->isDisabled());

        $this->client->request(
            'PUT',
            $this->router->generate('admin_page_update_disabled', ['id' => $pageOther->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);

        // Landing page : refusé
        $this->client->request(
            'PUT',
            $this->router->generate('admin_page_update_disabled', ['id' => $landingPage->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($this->translator->trans('page.error.landing.disabled', domain: 'page'), $content['msg']);
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        $user = $this->createUserContributeur();
        $page = $this->createPageAllDataDefault($user);
        $pageOther = $this->createPageWithTranslations(customData: ['disabled' => false]);
        $landingPage = $this->createPageWithTranslations($user, ['disabled' => false, 'landingPage' => true]);

        $this->checkNoAccess('admin_page_delete', ['id' => $page->getId()], 'DELETE');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_page_delete', ['id' => $page->getId()]);

        $this->client->request('DELETE', $url);
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getGridCsrfToken('delete');

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_page_delete', ['id' => $landingPage->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($this->translator->trans('page.error.landing.delete', domain: 'page'), $content['msg']);

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_page_delete', ['id' => $pageOther->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('DELETE', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);

        $this->em->clear();
        $pageRepository = $this->em->getRepository(Page::class);
        $this->assertNull($pageRepository->find($page->getId()));
        $this->assertNotNull($pageRepository->find($landingPage->getId()));

        // Admin : peut supprimer la page d'un autre
        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_page_delete', ['id' => $pageOther->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $this->getGridCsrfToken('delete')],
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test méthode switchLandingPage()
     * @return void
     */
    public function testSwitchLandingPage(): void
    {
        $user = $this->createUserContributeur();
        $page1 = $this->createPageWithTranslations($user, [
            'disabled' => false,
            'status' => PageStatus::PUBLISH->value,
        ]);
        $pageDraft = $this->createPageWithTranslations($user, [
            'disabled' => false,
            'status' => PageStatus::DRAFT->value,
        ]);
        $this->createPage();
        $this->createPage(customData: ['landingPage' => true]);

        $this->checkNoAccess('admin_page_switch_Landing_page', ['id' => $page1->getId()], 'PUT');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_page_switch_Landing_page', ['id' => $page1->getId()]);

        $this->client->request('PUT', $url);
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getGridCsrfToken('switch-landing-page');

        // Page non publiée : refusé
        $this->client->request(
            'PUT',
            $this->router->generate('admin_page_switch_Landing_page', ['id' => $pageDraft->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);

        $this->em->clear();
        foreach ($this->em->getRepository(Page::class)->findAll() as $page) {
            /** @var Page $page */
            $this->assertEquals($page->getId() === $page1->getId(), $page->isLandingPage());
        }
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd(): void
    {
        $user = $this->createUserContributeur();
        $page = $this->createPageAllDataDefault($user);
        $pageOther = $this->createPageAllDataDefault();

        $this->checkNoAccess('admin_page_add');
        $this->checkNoAccess('admin_page_update', ['id' => $page->getId()]);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.add.title', domain: 'page'));

        $this->client->request('GET', $this->router->generate('admin_page_update', ['id' => $page->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.update.title', domain: 'page'));

        $this->client->request('GET', $this->router->generate('admin_page_update', ['id' => $pageOther->getId()]));
        $this->assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_update', ['id' => $pageOther->getId()]));
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test méthode loadTabContent()
     * @return void
     */
    public function testLoadTabContent(): void
    {
        $this->checkNoAccess('admin_page_load_page');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_load_page'));

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('page', $content);
        $this->assertNull($content['page']['id']);

        $page = $this->createPageAllDataDefault($user);
        $this->client->request('GET', $this->router->generate('admin_page_load_page', ['id' => $page->getId()]));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($content['page']['id'], $page->getId());

        $pageOther = $this->createPageAllDataDefault();
        $this->client->request('GET', $this->router->generate('admin_page_load_page', ['id' => $pageOther->getId()]));
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test méthode autoSave()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAutoSave(): void
    {
        $this->checkNoAccess('admin_page_auto_save', methode: 'PUT');

        $data = ['page' => $this->getDataTest()];
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_page_auto_save');

        $this->client->request('PUT', $url, content: json_encode($data));
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getPropsCsrfToken('csrfTokenAutoSave');
        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);

        $containerBag = $this->container->get(ContainerBagInterface::class);
        $pageHistory = new PageHistory($containerBag, $user);
        $filesystem = new Filesystem();

        $path = $pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-user-' . $user->getId() . '.txt';
        $this->assertTrue($filesystem->exists($path));
        $pageHistory->removePageHistory();

        $page = $this->createPage($user);
        $data = ['page' => $this->getDataTest($page->getId())];
        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseIsSuccessful();

        $path = $pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-' . $page->getId() . '.txt';
        $this->assertTrue($filesystem->exists($path));
        $pageHistory->removePageHistory($page->getId());

        // Page d'un autre contributeur
        $pageOther = $this->createPage();
        $data = ['page' => $this->getDataTest($pageOther->getId())];
        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseStatusCodeSame(403);
        $path = $pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-' . $pageOther->getId() . '.txt';
        $this->assertFalse($filesystem->exists($path));
    }

    /**
     * Test méthode loadTabHistory()
     * @return void
     */
    public function testLoadTabHistory(): void
    {
        $this->checkNoAccess('admin_page_load_tab_history');

        $user = $this->createUserContributeur();
        $containerBag = $this->container->get(ContainerBagInterface::class);
        $pageHistory = new PageHistory($containerBag, $user);
        $pageHistory->save($this->getDataTest());

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_load_tab_history'));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('history', $content);
        $this->assertCount(1, $content['history']);
        $this->assertArrayHasKey('time', $content['history'][0]);
        $this->assertEquals(0, $content['history'][0]['id']);
        $this->assertArrayHasKey('user', $content['history'][0]);
        $pageHistory->removePageHistory();

        $page = $this->createPage($user);
        $pageHistory->save($this->getDataTest($page->getId()));
        $this->client->request('GET', $this->router->generate('admin_page_load_tab_history', ['id' => $page->getId()]));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $content['history']);
        $pageHistory->removePageHistory($page->getId());

        $pageOther = $this->createPage();
        $this->client->request(
            'GET',
            $this->router->generate('admin_page_load_tab_history', ['id' => $pageOther->getId()]),
        );
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test de la méthode reloadPageHistory()
     * @return void
     */
    public function testReloadPageHistory(): void
    {
        $this->checkNoAccess('admin_page_reload_page_history', methode: 'POST');

        $user = $this->createUserContributeur();
        $containerBag = $this->container->get(ContainerBagInterface::class);
        $pageHistory = new PageHistory($containerBag, $user);
        $pageHistory->save($this->getDataTest());

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_page_reload_page_history');
        $this->client->request('POST', $url, content: json_encode(['row_id' => 0, 'id' => null]));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('page', $content);
        $this->assertArrayHasKey('msg', $content);
        $this->assertTrue($content['success']);
        $pageHistory->removePageHistory();

        $this->client->request('GET', $url, content: json_encode(['row_id' => 0, 'id' => null]));
        $this->assertResponseStatusCodeSame(405);

        $pageOther = $this->createPage();
        $this->client->request('POST', $url, content: json_encode(['row_id' => 0, 'id' => $pageOther->getId()]));
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test méthode save()
     * @return void
     */
    public function testSave(): void
    {
        $this->checkNoAccess('admin_page_save', methode: 'POST');

        $user = $this->createUserContributeur();
        $page = $this->createPageAllDataDefault($user);
        $data = ['page' => $this->getDataTest($page->getId())];

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_page_save');

        $this->client->request('POST', $url, content: json_encode($data));
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getPropsCsrfToken('csrfTokenSave');
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('url_redirect', $content);
        $this->assertArrayHasKey('msg', $content);
        $this->assertTrue($content['success']);
        $this->assertFalse($content['redirect']);

        // Nouvelle page
        $dataNew = ['page' => $this->getDataTest()];
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($dataNew));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertTrue($content['redirect']);

        // URL déjà prise par une autre page dans la même locale
        $duplicate = $this->getDataTest();
        $duplicate['pageTranslations'][0]['url'] = $dataNew['page']['pageTranslations'][0]['url'];
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode(['page' => $duplicate]),
        );
        $this->assertResponseStatusCodeSame(400);

        // Valeur invalide
        $invalid = $data;
        $invalid['page']['status'] = 99;
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($invalid));
        $this->assertResponseStatusCodeSame(400);
        $this->assertStringContainsString('status', $this->client->getResponse()->getContent());

        // Landing page non publiée : refusé
        $landing = $data;
        $landing['page']['landingPage'] = true;
        $landing['page']['status'] = PageStatus::DRAFT->value;
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($landing));
        $this->assertResponseStatusCodeSame(400);

        // Retrait de la landing page sans en définir une autre : refusé
        $landingPage = $this->createPageWithTranslations($user, ['disabled' => false, 'landingPage' => true]);
        $removeLanding = ['page' => $this->getDataTest($landingPage->getId())];
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode($removeLanding),
        );
        $this->assertResponseStatusCodeSame(400);
        $this->em->clear();
        $this->assertTrue($this->em->getRepository(Page::class)->find($landingPage->getId())->isLandingPage());

        // Page d'un autre contributeur, y compris via l'id dans les données
        $pageOther = $this->createPageAllDataDefault();
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode(['page' => $this->getDataTest($pageOther->getId())]),
        );
        $this->assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $this->getPropsCsrfToken('csrfTokenSave')],
            content: json_encode(['page' => $this->getDataTest($pageOther->getId())]),
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test méthode newContent()
     * @return void
     */
    public function testNewContent(): void
    {
        $this->checkNoAccess('admin_page_new_content', methode: 'POST');

        $data = [
            'type' => PageContentType::TEXT->value,
            'type_id' => 0,
            'renderBlock' => 1,
        ];

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('POST', $this->router->generate('admin_page_new_content'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('pageContent', $content);
    }

    /**
     * Test méthode listeContentByIdContent()
     * @return void
     */
    public function testListeContentByIdContent(): void
    {
        $this->checkNoAccess('admin_page_liste_content_by_id', ['type' => PageContentType::TEXT->value]);
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_page_liste_content_by_id', ['type' => PageContentType::TEXT->value]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('list', $content);
        $this->assertArrayHasKey('selected', $content);
        $this->assertArrayHasKey('label', $content);
        $this->assertArrayHasKey('help', $content);

        $faq = $this->createFaqAllDataDefault();
        $this->client->request(
            'GET',
            $this->router->generate('admin_page_liste_content_by_id', ['type' => PageContentType::FAQ->value]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertCount(1, $content['list']);
        $this->assertArrayHasKey($faq->getId(), $content['list']);
    }

    /**
     * Test méthode isUniqueUrlPage()
     * @return void
     */
    public function testIsUniqueUrlPage(): void
    {
        $page = $this->createPageAllDataDefault();
        $pageOther = $this->createPageAllDataDefault();
        $url = $page->getPageTranslationByLocale('fr')->getUrl();

        $this->checkNoAccess('admin_page_is_unique_url_page', methode: 'POST');
        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $route = $this->router->generate('admin_page_is_unique_url_page');

        $check = function (string $url, string $locale, ?int $id): bool {
            $this->client->request(
                'POST',
                $this->router->generate('admin_page_is_unique_url_page'),
                content: json_encode(['url' => $url, 'locale' => $locale, 'id' => $id]),
            );
            $this->assertResponseIsSuccessful();
            return json_decode($this->client->getResponse()->getContent(), true)['is_unique'];
        };

        // Sa propre url
        $this->assertTrue($check($url, 'fr', $page->getId()));
        // Url d'une autre page dans la même locale
        $this->assertFalse($check($url, 'fr', $pageOther->getId()));
        $this->assertFalse($check($url, 'fr', null));
        // Autre locale
        $this->assertTrue($check($url, 'en', $pageOther->getId()));
        $this->assertTrue($check('toto-url', 'fr', $page->getId()));
    }

    /**
     * Test méthode getInfoRenderBlock()
     * @return void
     */
    public function testGetInfoRenderBlock(): void
    {
        $faq = $this->createFaqAllDataDefault();
        $this->checkNoAccess('admin_page_info_render_block');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');

        $this->client->request(
            'GET',
            $this->router->generate('admin_page_info_render_block', [
                'type' => PageContentType::FAQ->value,
                'typeId' => $faq->getId(),
            ]),
        );
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('type', $content);
        $this->assertArrayHasKey('info', $content);
        $this->assertStringContainsString($faq->getFaqTranslationByLocale('fr')->getTitle(), $content['info']);
    }

    /**
     * Test méthode getListePageForInternalLinks()
     * @return void
     */
    public function testGetListePageForInternalLinks(): void
    {
        $page = $this->createPageAllDataDefault();
        $this->checkNoAccess('admin_page_liste_pages_internal_link');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', $this->router->generate('admin_page_liste_pages_internal_link'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('pages', $content);
        $this->assertCount(1, $content['pages']);
        $this->assertArrayHasKey($page->getId(), $content['pages']);
    }

    /**
     * Retourne un jeu de données de test
     * @param $idPage
     * @return array
     */
    private function getDataTest($idPage = null): array
    {
        $tag1 = $this->createTag();
        $tag2 = $this->createTag();
        $tag3 = $this->createTag();
        $menu = $this->createMenuAllDataDefault();
        $uniq = uniqid();

        return [
            'id' => $idPage,
            'render' => 1,
            'status' => 1,
            'pageMeta' => [
                'id' => 1,
                'page' => 1,
                'name' => 'description',
                'pageMetaTranslations' => [
                    [
                        'id' => 1,
                        'pageMeta' => 1,
                        'locale' => 'fr',
                        'value' => 'Page présentation',
                    ],
                    [
                        'id' => 1,
                        'pageMeta' => 1,
                        'locale' => 'es',
                        'value' => 'es-Page présentation',
                    ],
                    [
                        'id' => 1,
                        'pageMeta' => 1,
                        'locale' => 'en',
                        'value' => 'en-Page présentation',
                    ],
                ],
            ],
            'pageTranslations' => [
                [
                    'id' => 1,
                    'page' => 1,
                    'locale' => 'fr',
                    'titre' => 'Dernières pages',
                    'url' => 'pages-' . $uniq,
                ],
                [
                    'id' => 2,
                    'page' => 1,
                    'locale' => 'es',
                    'titre' => 'Últimas páginas',
                    'url' => 'paginas-' . $uniq,
                ],
                [
                    'id' => 3,
                    'page' => 1,
                    'locale' => 'en',
                    'titre' => 'Last pages',
                    'url' => 'page-' . $uniq,
                ],
            ],
            'pageContents' => [
                [
                    'id' => 23,
                    'page' => 1,
                    'renderOrder' => 1,
                    'type' => 1,
                    'pageContentTranslations' => [
                        [
                            'id' => 37,
                            'pageContent' => 23,
                            'locale' => 'fr',
                            'text' => '[fr] Contenu de votre page',
                        ],
                        [
                            'id' => 38,
                            'pageContent' => 23,
                            'locale' => 'en',
                            'text' => '[en] Contenu de votre page',
                        ],
                        [
                            'id' => 39,
                            'pageContent' => 23,
                            'locale' => 'es',
                            'text' => '[es] Contenu de votre page',
                        ],
                    ],
                    'typeId' => null,
                    'renderBlock' => 1,
                ],
            ],
            'pageStatistiques' => [
                [
                    'id' => 1,
                    'page' => 1,
                    'key' => 'PAGE_NB_VISITEUR',
                    'value' => '100',
                ],
                [
                    'id' => 2,
                    'page' => 1,
                    'key' => 'PAGE_NB_READ',
                    'value' => '32',
                ],
            ],
            'disabled' => false,
            'category' => 1,
            'landingPage' => false,
            'openComment' => true,
            'nbComment' => 10,
            'ruleComment' => 2,
            'headerImg' => null,
            'menus' => [$menu->getId()],
            'tags' => [
                [
                    'id' => $tag1->getId(),
                    'color' => '#6F42C1',
                    'disabled' => false,
                    'tagTranslations' => [
                        [
                            'id' => 1,
                            'tag' => 1,
                            'locale' => 'fr',
                            'label' => 'Natheo',
                        ],
                        [
                            'id' => 2,
                            'tag' => 1,
                            'locale' => 'es',
                            'label' => 'Natheo',
                        ],
                        [
                            'id' => 3,
                            'tag' => 1,
                            'locale' => 'en',
                            'label' => 'Natheo',
                        ],
                    ],
                ],
                [
                    'id' => $tag2->getId(),
                    'color' => '#23e515',
                    'disabled' => false,
                    'tagTranslations' => [
                        [
                            'id' => 22,
                            'tag' => 8,
                            'locale' => 'fr',
                            'label' => 'Page',
                        ],
                        [
                            'id' => 23,
                            'tag' => 8,
                            'locale' => 'es',
                            'label' => 'Page (ES)',
                        ],
                        [
                            'id' => 24,
                            'tag' => 8,
                            'locale' => 'en',
                            'label' => 'Page (EN)',
                        ],
                    ],
                ],
                [
                    'id' => $tag3->getId(),
                    'color' => '#00478c',
                    'disabled' => false,
                    'tagTranslations' => [
                        [
                            'id' => 28,
                            'tag' => 10,
                            'locale' => 'fr',
                            'label' => 'toto',
                        ],
                        [
                            'id' => 29,
                            'tag' => 10,
                            'locale' => 'en',
                            'label' => 'toto (en)',
                        ],
                        [
                            'id' => 30,
                            'tag' => 10,
                            'locale' => 'es',
                            'label' => 'toto (es)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Crée une page avec une traduction par locale
     * @param User|null $user
     * @param array $customData
     * @return Page
     */
    private function createPageWithTranslations(?User $user = null, array $customData = []): Page
    {
        $page = $this->createPage($user, $customData);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }
        return $page;
    }

    /**
     * Retourne le premier jeton CSRF trouvé dans les actions de la grille dont l'URL contient $urlPart
     * @param string $urlPart
     * @return string
     */
    private function getGridCsrfToken(string $urlPart): string
    {
        $this->client->request(
            'GET',
            $this->router->generate('admin_page_load_grid_data', ['page' => 1, 'limit' => 50]),
        );
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
     * Retourne un jeton CSRF passé au composant Vue Page (page d'ajout)
     * @param string $key csrfTokenSave ou csrfTokenAutoSave
     * @return string
     */
    private function getPropsCsrfToken(string $key): string
    {
        $this->client->request('GET', $this->router->generate('admin_page_add'));
        $props = $this->client
            ->getCrawler()
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/Content/Page/Page"]')
            ->attr('data-symfony--ux-vue--vue-props-value');
        return json_decode($props, true)[$key];
    }
}
