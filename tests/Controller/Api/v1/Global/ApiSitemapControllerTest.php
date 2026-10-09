<?php

declare(strict_types=1);
/**
 * Test unitaire API sitemap
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Controller\Api\v1\Global;

use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Tests\Controller\Api\AppApiTestCase;

class ApiSitemapControllerTest extends AppApiTestCase
{
    /**
     * Test la méthode getSitemap()
     * @return void
     */
    public function testGetSitemap(): void
    {
        $verif = [];
        for ($i = 0; $i < 3; $i++) {
            $verif[] = $this->createPageAllDataDefault();
        }
        // Catégorie accentuée : l'URL doit utiliser le slug ASCII attendu par le front
        $verif[0]->setCategory(PageCategory::EVENEMENT->value);
        $this->persistAndFlush($verif[0]);

        // Une page désactivée ne doit pas apparaître dans le sitemap
        $disabledPage = $this->createPageAllDataDefault();
        $disabledPage->setDisabled(true);
        $this->persistAndFlush($disabledPage);

        $this->client->request(
            'GET',
            $this->router->generate('api_sitemap_sitemap', ['api_version' => self::API_VERSION, 'locale' => 'fr']),
            server: $this->getCustomHeaders(),
        );
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        $this->assertCount($i * 3, $content['data']);

        $evenementUrls = array_filter($content['data'], fn($item) => str_contains($item['loc'], '/evenement/'));
        $this->assertCount(3, $evenementUrls);

        foreach ($content['data'] as $item) {
            $this->assertMatchesRegularExpression('/^[\x00-\x7F]*$/', $item['loc']);
            foreach ($verif as $page) {
                /** @var Page $page */
                foreach ($page->getPageTranslations() as $pageTranslation) {
                    $url =
                        '/' .
                        $pageTranslation->getLocale() .
                        '/' .
                        PageCategory::from($page->getCategory())->getSlug() .
                        '/' .
                        $pageTranslation->getUrl();
                    if (str_contains($item['loc'], $pageTranslation->getUrl()) === true) {
                        $this->assertEquals($url, $item['loc']);
                        $this->assertEquals('1.00', $item['priority']);
                        $this->assertEquals($page->getUpdateAt()->format(DATE_ATOM), $item['lastmod']);
                    }
                }
            }
        }
    }
}
