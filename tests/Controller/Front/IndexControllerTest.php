<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller front Index
 */
namespace App\Tests\Controller\Front;

use App\Entity\Admin\System\UserSession;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Global\Token\TokenHasher;
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
}
