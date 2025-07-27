<?php
/**
 * Test unitaire API sitemap
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Controller\Api\v1\Global;

use App\Entity\Admin\Content\Page\Page;
use App\Service\Admin\Content\Page\PageService;
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

        $this->client->request('GET', $this->router->generate('api_sitemap_sitemap', ['api_version' => self::API_VERSION, 'locale' => 'fr']),
            server: $this->getCustomHeaders()
        );
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->checkStructureApiRetour($content);

        $this->assertCount($i * 3, $content['data']);

        $pageService = $this->getContainer()->get(PageService::class);

        foreach ($content['data'] as $item) {
            foreach ($verif as $page) {
                /** @var Page $page */
                foreach ($page->getPageTranslations() as $pageTranslation) {
                    $url = '/' . $pageTranslation->getLocale() . '/' . strtolower($pageService->getCategoryById($page->getCategory())) . '/' . $pageTranslation->getUrl();
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