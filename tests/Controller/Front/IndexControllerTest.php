<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller front Index
 */
namespace App\Tests\Controller\Front;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageDraft;
use App\Entity\Admin\System\UserSession;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\Content\Page\PageRender;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Front\Template;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Global\Token\TokenHasher;
use App\Tests\AppWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class IndexControllerTest extends AppWebTestCase
{
    /**
     * Page d'accueil par locale : rendu du composant Vue avec le token API du front
     * @return void
     */
    public function testIndex(): void
    {
        $this->createUser();

        foreach ($this->locales as $locale) {
            $this->client->request(
                'GET',
                $this->router->generate('front_index_2', ['locale' => $locale, 'category' => null, 'slug' => null]),
            );
            $this->assertResponseIsSuccessful();
            $this->assertSelectorExists('html[lang="' . $locale . '"]');

            $content = (string) $this->client->getResponse()->getContent();
            $this->assertStringContainsString('Front/NatheoHorizon/Structure', $content);
            $this->assertStringContainsString('front-token-test', $content);
        }
    }

    /**
     * Utilisateur connecté au back-office : une seule session API, réutilisée d'une page à l'autre
     * @return void
     */
    public function testIndexLoggedUser(): void
    {
        $this->createApiToken(['token' => TokenHasher::hash('front-token-test'), 'disabled' => false]);
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $this->generateDefaultOptionUser($user);
        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('front_index_2', ['locale' => 'fr', 'category' => null, 'slug' => null]);

        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        $sessions = $this->em->getRepository(UserSession::class)->findBy(['user' => $user->getId()]);
        $this->assertCount(1, $sessions);
    }

    /**
     * Une locale non supportée ne correspond à aucune route
     * @return void
     */
    public function testIndexUnsupportedLocale(): void
    {
        $this->createUser();
        $this->client->request('GET', '/de/');
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Site fermé : page de maintenance en 503
     * @return void
     */
    public function testIndexCloseSite(): void
    {
        $this->createUser();
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_OPEN_SITE->value, '0');

        $this->client->request(
            'GET',
            $this->router->generate('front_index_2', ['locale' => 'fr', 'category' => null, 'slug' => null]),
        );
        $this->assertResponseStatusCodeSame(503);
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('front_error.closed_site.block_title', domain: 'front_error'),
        );
    }

    /**
     * Les options robots sont regroupées dans une seule balise meta
     * @return void
     */
    public function testIndexMetaRobots(): void
    {
        $this->createUser();
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystem::OS_FRONT_ROBOT_NO_INDEX->value, '1');
        $optionSystemService->saveValueByKee(OptionSystem::OS_FRONT_ROBOT_NO_FOLLOW->value, '1');

        $crawler = $this->client->request(
            'GET',
            $this->router->generate('front_index_2', ['locale' => 'fr', 'category' => null, 'slug' => null]),
        );
        $robots = $crawler->filter('meta[name="robots"]');
        $this->assertCount(1, $robots);
        $this->assertEquals('noindex, nofollow', $robots->attr('content'));
    }

    /**
     * Sitemap au format XML
     * @return void
     */
    public function testSitemap(): void
    {
        $this->createPageAllDataDefault();
        $this->client->request('GET', $this->router->generate('front_sitemap'));
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/xml; charset=UTF-8');

        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringNotContainsString('http://localhosthttp', $content);
    }

    /**
     * Thème sur l'API v2 : props communes, sans utilisateur ni prévisualisation pour un visiteur
     * @return void
     */
    public function testIndexApiV2Theme(): void
    {
        $this->createUser();
        $this->setTheme(Template::NATHEO_BASE);

        $crawler = $this->client->request(
            'GET',
            $this->router->generate('front_index_2', ['locale' => 'en', 'category' => null, 'slug' => null]),
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Front/NatheoBase/App', (string) $this->client->getResponse()->getContent());

        $props = $this->getVueProps($crawler);
        $this->assertEquals($this->container->getParameter('app.api_v2.prefix'), $props['urls']['api']);
        $this->assertEquals('/fr/', $props['urls']['home']['fr']);
        $this->assertEquals('en', $props['datas']['locale']);
        $this->assertEquals(['fr', 'en', 'es'], $props['datas']['locales']);
        $this->assertEquals('front-token-test', $props['datas']['apiToken']);
        $this->assertEquals('', $props['datas']['userToken']);
        $this->assertNull($props['datas']['user']);
        $this->assertNull($props['datas']['preview']);
        $this->assertArrayNotHasKey('apiPageFind', $props['urls']);
    }

    /**
     * Lien « Modifier cette page » : seulement pour un utilisateur qui peut modifier la page affichée
     * @return void
     */
    public function testIndexApiV2ThemeEditLink(): void
    {
        $owner = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $other = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $page = $this->createPageAllDataDefault($owner);
        $this->setTheme(Template::NATHEO_BASE);
        $url = $this->pageUrl($page);
        $editUrl = $this->router->generate('admin_page_update', ['_locale' => 'fr', 'id' => $page->getId()]);

        $this->client->loginUser($owner, 'admin');
        $props = $this->getVueProps($this->client->request('GET', $url));
        $this->assertEquals($editUrl, $props['datas']['user']['editUrl']);
        $this->assertEquals($owner->getLogin(), $props['datas']['user']['login']);

        $this->client->loginUser($other, 'admin');
        $props = $this->getVueProps($this->client->request('GET', $url));
        $this->assertNull($props['datas']['user']['editUrl']);
    }

    /**
     * Prévisualisation : brouillon d'une page publiée sur demande, page non publiée directement,
     * uniquement pour un utilisateur qui peut la modifier
     * @return void
     */
    public function testIndexApiV2ThemePreview(): void
    {
        $owner = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $other = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $page = $this->createPageAllDataDefault($owner);
        $unpublished = $this->createPageAllDataDefault($owner);
        $this->persistAndFlush($unpublished->setStatus(PageStatus::DRAFT->value));
        $this->createDraft($page);
        $this->setTheme(Template::NATHEO_BASE);
        $url = $this->pageUrl($page);

        // Sans demande de prévisualisation ou sans droit : la page en ligne, servie par l'API
        $this->client->loginUser($owner, 'admin');
        $this->assertNull($this->getVueProps($this->client->request('GET', $url))['datas']['preview']);
        $this->client->loginUser($other, 'admin');
        $this->assertNull($this->getVueProps($this->client->request('GET', $url . '?preview=1'))['datas']['preview']);

        $this->client->loginUser($owner, 'admin');
        $preview = $this->getVueProps($this->client->request('GET', $url . '?preview=1'))['datas']['preview'];
        $this->assertEquals('Brouillon fr', $preview['page']['title']);
        $this->assertEquals($page->getId(), $preview['page']['id']);
        $this->assertEquals('/api/v2/pages/' . $page->getId(), $preview['page']['@id']);
        $this->assertEquals('one_block', $preview['page']['render']);
        $this->assertCount(1, $preview['page']['contents']);
        $this->assertEquals(-1, $preview['page']['contents'][0]['id']);
        $this->assertEquals(-1, $preview['contents'][0]['id']);
        $this->assertEquals('text', $preview['contents'][0]['type']);
        $this->assertStringContainsString('Texte du brouillon', $preview['contents'][0]['content']['html']);

        $preview = $this->getVueProps($this->client->request('GET', $this->pageUrl($unpublished)))['datas']['preview'];
        $this->assertEquals($unpublished->getId(), $preview['page']['id']);
        $this->assertEquals(count($preview['page']['contents']), count($preview['contents']));

        // La page en ligne et ses blocs ne sont pas modifiés par la prévisualisation
        $this->em->clear();
        $pageOnline = $this->em->getRepository(Page::class)->find($page->getId());
        $this->assertNotEquals('Brouillon fr', $pageOnline->getPageTranslationByLocale('fr')->getTitre());
        $this->assertNotCount(1, $pageOnline->getPageContents());
    }

    /**
     * Active un thème du site public
     * @param Template $template
     * @return void
     */
    private function setTheme(Template $template): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(OptionSystem::OS_THEME_FRONT_SITE->value, $template->value);
    }

    /**
     * Props passées au composant Vue racine du thème
     * @param Crawler $crawler
     * @return array
     */
    private function getVueProps(Crawler $crawler): array
    {
        $this->assertResponseIsSuccessful();
        return json_decode(
            (string) $crawler
                ->filter('[data-symfony--ux-vue--vue-props-value]')
                ->attr('data-symfony--ux-vue--vue-props-value'),
            true,
        );
    }

    /**
     * URL publique d'une page en français
     * @param Page $page
     * @return string
     */
    private function pageUrl(Page $page): string
    {
        return $this->router->generate('front_index_2', [
            'locale' => 'fr',
            'category' => PageCategory::from($page->getCategory())->getSlug(),
            'slug' => $page->getPageTranslationByLocale('fr')->getUrl(),
        ]);
    }

    /**
     * Brouillon d'une page publiée : titres « Brouillon <locale> » et un seul bloc texte
     * @param Page $page
     * @return void
     */
    private function createDraft(Page $page): void
    {
        $translations = [];
        $contentTranslations = [];
        foreach ($page->getPageTranslations() as $translation) {
            $translations[] = [
                'locale' => $translation->getLocale(),
                'titre' => 'Brouillon ' . $translation->getLocale(),
                'url' => $translation->getUrl(),
            ];
            $contentTranslations[] = ['locale' => $translation->getLocale(), 'text' => 'Texte du brouillon'];
        }
        $draft = (new PageDraft())->setPage($page)->setData([
            'id' => $page->getId(),
            'render' => PageRender::ONE_BLOCK->value,
            'status' => PageStatus::PUBLISH->value,
            'category' => $page->getCategory(),
            'ruleComment' => $page->getRuleComment(),
            'landingPage' => false,
            'isOpenComment' => true,
            'pageTranslations' => $translations,
            'pageContents' => [
                [
                    'renderBlock' => 1,
                    'renderOrder' => 1,
                    'type' => PageContentType::TEXT->value,
                    'pageContentTranslations' => $contentTranslations,
                ],
            ],
            'tags' => [],
            'menus' => [],
        ]);
        $this->persistAndFlush($draft);
    }
}
