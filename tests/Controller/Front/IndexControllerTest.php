<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller front Index
 */
namespace App\Tests\Controller\Front;

use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;

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
            $this->translator->trans('front.error.close.site.block.title', domain: 'front_error'),
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
}
