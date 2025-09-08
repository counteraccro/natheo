<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller Page
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Page\Page;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageHistory;
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
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.index.page_title_h1', domain: 'page'));
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $page = $this->createPage(customData: ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page, ['locale' => $locale]);
        }
        $pageTranslation = $page->getPageTranslationByLocale('fr');

        $this->checkNoAccess('admin_page_update_disabled', ['id' => $page->getId()], 'PUT');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_page_update_disabled', ['id' => $page->getId()]));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertStringContainsString($pageTranslation->getTitre(), $content['msg']);

        $pageRepository = $this->em->getRepository(Page::class);
        /** @var Page $result */
        $result = $pageRepository->findOneBy(['id' => $page->getId()]);
        $this->assertEquals($result->isDisabled(), !$page->isDisabled());
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        $page = $this->createPageAllDataDefault();
        $pageTranslation = $page->getPageTranslationByLocale('fr');

        $this->checkNoAccess('admin_page_delete', ['id' => $page->getId()], 'DELETE');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('DELETE', $this->router->generate('admin_page_delete', ['id' => $page->getId()]));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertStringContainsString($pageTranslation->getTitre(), $content['msg']);

        $pageRepository = $this->em->getRepository(Page::class);
        /** @var Page $result */
        $result = $pageRepository->findOneBy(['id' => $page->getId()]);
        $this->assertNull($result);
    }

    /**
     * Test méthode switchLandingPage()
     * @return void
     */
    public function testSwitchLandingPage(): void
    {
        $page1 = $this->createPage();
        foreach ($this->locales as $locale) {
            $this->createPageTranslation($page1, ['locale' => $locale]);
        }
        $this->createPage();
        $this->createPage();
        $this->createPage(customData: ['landingPage' => true]);

        $pageTranslation = $page1->getPageTranslationByLocale('fr');

        $this->checkNoAccess('admin_page_switch_Landing_page', ['id' => $page1->getId()], 'PUT');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_page_switch_Landing_page', ['id' => $page1->getId()]));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertStringContainsString($pageTranslation->getTitre(), $content['msg']);

        $pageRepository = $this->em->getRepository(Page::class);
        $result = $pageRepository->findAll();
        foreach ($result as $page) {
            /** @var Page $page */
            if ($page->getId() == $page1->getId()) {
                $this->assertTrue($page->isLandingPage());
            } else {
                $this->assertFalse($page->isLandingPage());
            }
        }
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd(): void
    {
        $page = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_page_add');
        $this->checkNoAccess('admin_page_update', ['id' => $page->getId()]);

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.add.page_title_h1', domain: 'page'));

        $this->client->request('GET', $this->router->generate('admin_page_update', ['id' => $page->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('page.update.page_title_h1', domain: 'page'));
    }

    /**
     * Test méthode loadTabContent()
     * @return void
     */
    public function testLoadTabContent(): void
    {

        $this->checkNoAccess('admin_page_load_tab_content');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_load_tab_content'));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('page', $content);
        $this->assertNull($content['page']['id']);

        $page = $this->createPageAllDataDefault();
        $this->client->request('GET', $this->router->generate('admin_page_load_tab_content', ['id' => $page->getId()]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('page', $content);
        $this->assertEquals($content['page']['id'], $page->getId());
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
        $this->client->request('PUT', $this->router->generate('admin_page_auto_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $containerBag = $this->container->get(ContainerBagInterface::class);
        $pageHistory = new PageHistory($containerBag, $user);
        $filesystem = new Filesystem();

        $path = $pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-user-' . $user->getId() . '.txt';
        $exist = $filesystem->exists($path);
        $this->assertTrue($exist);
        $pageHistory->removePageHistory();

        $page = $this->createPage();
        $data = ['page' => $this->getDataTest($page->getId())];
        $this->client->request('PUT', $this->router->generate('admin_page_auto_save'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertTrue($content['success']);

        $path = $pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-' . $page->getId() . '.txt';
        $exist = $filesystem->exists($path);
        $this->assertTrue($exist);
        $pageHistory->removePageHistory();
    }

    /**
     * Test méthode loadTabHistory()
     * @return void
     */
    public function testLoadTabHistory(): void
    {
        $this->checkNoAccess('admin_page_load_tab_history');

        $data = ['page' => $this->getDataTest()];
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_page_auto_save'), content: json_encode($data));
        $this->client->request('GET', $this->router->generate('admin_page_load_tab_history'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('history', $content);
        $this->assertCount(1, $content);
        $this->assertArrayHasKey('time', $content['history'][0]);
        $this->assertArrayHasKey('id', $content['history'][0]);
        $this->assertEquals(0, $content['history'][0]['id']);
        $this->assertArrayHasKey('user', $content['history'][0]);

        $containerBag = $this->container->get(ContainerBagInterface::class);
        $pageHistory = new PageHistory($containerBag, $user);
        $pageHistory->removePageHistory();

        $data = ['page' => $this->getDataTest(99)];
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_page_auto_save'), content: json_encode($data));
        $this->client->request('GET', $this->router->generate('admin_page_load_tab_history', ['id' => 99]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('history', $content);
        $this->assertCount(1, $content);
        $this->assertArrayHasKey('time', $content['history'][0]);
        $this->assertArrayHasKey('id', $content['history'][0]);
        $this->assertEquals(0, $content['history'][0]['id']);
        $this->assertArrayHasKey('user', $content['history'][0]);
        $pageHistory->removePageHistory(99);
    }

    /**
     * Test de la méthode reloadPageHistory()
     * @return void
     */
    public function testReloadPageHistory(): void
    {
        $this->checkNoAccess('admin_page_reload_page_history');

        $data = ['page' => $this->getDataTest()];
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('PUT', $this->router->generate('admin_page_auto_save'), content: json_encode($data));
        $this->client->request('GET', $this->router->generate('admin_page_reload_page_history'), content: json_encode(['row_id' => 0, 'id' => null]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertArrayHasKey('page', $content);
        $this->assertArrayHasKey('msg', $content);
        $this->assertTrue($content['success']);

        $containerBag = $this->container->get(ContainerBagInterface::class);
        $pageHistory = new PageHistory($containerBag, $user);
        $pageHistory->removePageHistory();
    }

    /**
     * Test méthode save()
     * @return void
     */
    public function testSave(): void
    {
        $this->checkNoAccess('admin_page_save', methode: 'POST');

        $page = $this->createPageAllDataDefault();
        $data = ['page' => $this->getDataTest($page->getId())];
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('POST', $this->router->generate('admin_page_save'), content: json_encode($data));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertArrayHasKey('url_redirect', $content);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('redirect', $content);
        $this->assertTrue($content['success']);
        $this->assertFalse($content['redirect']);

        // Nouvelle page
        $data = ['page' => $this->getDataTest()];
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('POST', $this->router->generate('admin_page_save'), content: json_encode($data));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('success', $content);
        $this->assertArrayHasKey('url_redirect', $content);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('redirect', $content);
        $this->assertTrue($content['success']);
        $this->assertTrue($content['redirect']);
    }

    /**
     * Test méthode newContent()
     * @return void
     */
    public function testNewContent(): void
    {
        $this->checkNoAccess('admin_page_new_content', methode: 'POST');

        $data = [
            "type" => PageConst::CONTENT_TYPE_TEXT,
            "type_id" => 0,
            "renderBlock" => 1
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
        $this->checkNoAccess('admin_page_liste_content_by_id', ['type' => PageConst::CONTENT_TYPE_TEXT]);
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_liste_content_by_id', ['type' => PageConst::CONTENT_TYPE_TEXT]));
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
        $this->client->request('GET', $this->router->generate('admin_page_liste_content_by_id', ['type' => PageConst::CONTENT_TYPE_FAQ]));
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

        $this->checkNoAccess('admin_page_is_unique_url_page', methode: 'POST');
        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');

        $data = [
            'url' => $page->getPageTranslationByLocale('fr')->getUrl(),
            'id' => $page->getId()
        ];

        $this->client->request('POST', $this->router->generate('admin_page_is_unique_url_page'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('is_unique', $content);
        $this->assertTrue($content['is_unique']);

        $data = [
            'url' => 'toto-url',
            'id' => $page->getId()
        ];

        $this->client->request('POST', $this->router->generate('admin_page_is_unique_url_page'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('is_unique', $content);
        $this->assertFalse($content['is_unique']);
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

        $this->client->request('GET', $this->router->generate('admin_page_info_render_block', ['type' => PageConst::CONTENT_TYPE_FAQ, 'typeId' => $faq->getId()]));
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
     * Test méthode preview()
     * @return void
     */
    public function testPreview(): void
    {
        $page = $this->createPageAllDataDefault();
        $this->checkNoAccess('admin_page_preview');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_page_preview', ['id' => $page->getId(), 'locale' => 'fr']));
        $this->assertResponseIsSuccessful();
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

        return [
            "id" => $idPage,
            "render" => 1,
            "status" => 1,
            "pageMeta" => [
                "id" => 1,
                "page" => 1,
                "name" => "description",
                "pageMetaTranslations" => [
                    [
                        "id" => 1,
                        "pageMeta" => 1,
                        "locale" => "fr",
                        "value" => "Page présentation"
                    ],
                    [
                        "id" => 1,
                        "pageMeta" => 1,
                        "locale" => "es",
                        "value" => "es-Page présentation"
                    ],
                    [
                        "id" => 1,
                        "pageMeta" => 1,
                        "locale" => "en",
                        "value" => "en-Page présentation"
                    ],
                ],
            ],
            "pageTranslations" => [
                [
                    "id" => 1,
                    "page" => 1,
                    "locale" => "fr",
                    "titre" => "Dernières pages",
                    "url" => "pages"
                ],
                [
                    "id" => 2,
                    "page" => 1,
                    "locale" => "es",
                    "titre" => "Últimas páginas",
                    "url" => "paginas"
                ],
                [
                    "id" => 3,
                    "page" => 1,
                    "locale" => "en",
                    "titre" => "Last pages",
                    "url" => "page"
                ]
            ],
            "pageContents" => [
                [
                    "id" => 23,
                    "page" => 1,
                    "renderOrder" => 1,
                    "type" => 1,
                    "pageContentTranslations" => [
                        [
                            "id" => 37,
                            "pageContent" => 23,
                            "locale" => "fr",
                            "text" => "[fr] Contenu de votre page"
                        ],
                        [
                            "id" => 38,
                            "pageContent" => 23,
                            "locale" => "en",
                            "text" => "[en] Contenu de votre page"
                        ],
                        [
                            "id" => 39,
                            "pageContent" => 23,
                            "locale" => "es",
                            "text" => "[es] Contenu de votre page"
                        ]
                    ],
                    "typeId" => null,
                    "renderBlock" => 1
                ]
            ],
            "pageStatistiques" => [
                [
                    "id" => 1,
                    "page" => 1,
                    "key" => "PAGE_NB_VISITEUR",
                    "value" => "100"
                ],
                [
                    "id" => 2,
                    "page" => 1,
                    "key" => "PAGE_NB_READ",
                    "value" => "32"
                ]
            ],
            "disabled" => false,
            "category" => 1,
            "landingPage" => false,
            "openComment" => true,
            "nbComment" => 10,
            "ruleComment" => 2,
            'headerImg' => null,
            "menus" => [
                $menu->getId()
            ],
            "tags" => [
                [
                    "id" => $tag1->getId(),
                    "color" => "#6F42C1",
                    "disabled" => false,
                    "tagTranslations" => [
                        [
                            "id" => 1,
                            "tag" => 1,
                            "locale" => "fr",
                            "label" => "Natheo"
                        ],
                        [
                            "id" => 2,
                            "tag" => 1,
                            "locale" => "es",
                            "label" => "Natheo"
                        ],
                        [
                            "id" => 3,
                            "tag" => 1,
                            "locale" => "en",
                            "label" => "Natheo"
                        ]
                    ]
                ],
                [
                    "id" => $tag2->getId(),
                    "color" => "#23e515",
                    "disabled" => false,
                    "tagTranslations" => [
                        [
                            "id" => 22,
                            "tag" => 8,
                            "locale" => "fr",
                            "label" => "Page"
                        ],
                        [
                            "id" => 23,
                            "tag" => 8,
                            "locale" => "es",
                            "label" => "Page (ES)"
                        ],
                        [
                            "id" => 24,
                            "tag" => 8,
                            "locale" => "en",
                            "label" => "Page (EN)"
                        ]
                    ]
                ],
                [
                    "id" => $tag3->getId(),
                    "color" => "#00478c",
                    "disabled" => false,
                    "tagTranslations" => [
                        [
                            "id" => 28,
                            "tag" => 10,
                            "locale" => "fr",
                            "label" => "toto"
                        ],
                        [
                            "id" => 29,
                            "tag" => 10,
                            "locale" => "en",
                            "label" => "toto (en)"
                        ],
                        [
                            "id" => 30,
                            "tag" => 10,
                            "locale" => "es",
                            "label" => "toto (es)"
                        ]
                    ]
                ]
            ]
        ];
    }
}