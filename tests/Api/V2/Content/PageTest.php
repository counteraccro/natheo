<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource Page de l'API v2
 */

namespace App\Tests\Api\V2\Content;

use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Content\Page\PageStatistics;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Tests\Api\V2\AppApiV2TestCase;

class PageTest extends AppApiV2TestCase
{
    /**
     * Page complète par son id : champs, blocs et menus ; pas de lecture comptée
     * @return void
     */
    public function testGet(): void
    {
        $page = $this->createPageAllDataDefault();
        $menu = $this->createMenuAllDataDefault();
        $page->addMenu($menu);
        $this->persistAndFlush($page);
        $reads = (int) $page->getPageStatistiqueByKey(PageStatistics::NB_READ->value)->getValue();
        $translation = $page->getPageTranslationByLocale('fr');

        $content = $this->decode($this->requestV2('GET', '/pages/' . $page->getId() . '?locale=fr'));

        $this->assertEquals('/api/v2/pages/' . $page->getId(), $content['@id']);
        $this->assertEquals('Page', $content['@type']);
        $this->assertEquals($translation->getTitre(), $content['title']);
        $this->assertEquals($translation->getUrl(), $content['slug']);
        $this->assertEquals('page', $content['category']);
        $this->assertEquals('two_block_bottom', $content['render']);
        $this->assertTrue($content['openComment']);
        $this->assertEquals($reads, $content['reads']);
        $this->assertNotFalse(\DateTimeImmutable::createFromFormat(\DATE_ATOM, $content['updatedAt']));
        $this->assertArrayHasKey('name', $content['author']);
        $activeTags = $page->getTags()->filter(fn($tag) => !$tag->isDisabled());
        $this->assertCount($activeTags->count(), $content['tags']);
        $this->assertCount($page->getPageMetas()->count(), $content['seo']);
        $this->assertEquals(['@type', 'name', 'value'], array_keys($content['seo'][0]));

        $this->assertCount($page->getPageContents()->count(), $content['contents']);
        $block = $content['contents'][0];
        $this->assertEquals('/api/v2/page_contents/' . $page->getPageContents()->first()->getId(), $block['@id']);
        $this->assertArrayHasKey('type', $block);
        $this->assertArrayHasKey('position', $block);
        $this->assertArrayNotHasKey('content', $block);

        $menuIds = array_column($content['menus'], 'id');
        $this->assertContains($menu->getId(), $menuIds);
    }

    /**
     * Page par son id, dans la langue demandée
     * @return void
     */
    public function testGetByIdAndLocale(): void
    {
        $page = $this->createPageAllDataDefault();

        $content = $this->decode($this->requestV2('GET', '/pages/' . $page->getId() . '?locale=en'));
        $this->assertEquals($page->getId(), $content['id']);
        $this->assertEquals($page->getPageTranslationByLocale('en')->getTitre(), $content['title']);

        $this->assertEquals(422, $this->requestV2('GET', '/pages/' . $page->getId() . '?locale=de')->getStatusCode());
    }

    /**
     * Langue : paramètre locale prioritaire, puis Accept-Language, puis langue par défaut ; Content-Language renvoyé
     * @return void
     */
    public function testLocaleNegotiation(): void
    {
        $page = $this->createPageAllDataDefault();
        $path = '/pages/' . $page->getId();

        $response = $this->requestV2('GET', $path, headers: ['HTTP_ACCEPT_LANGUAGE' => 'de-DE,es-ES;q=0.9,en;q=0.8']);
        $this->assertEquals($page->getPageTranslationByLocale('es')->getTitre(), $this->decode($response)['title']);
        $this->assertEquals('es', $response->headers->get('Content-Language'));
        $this->assertContains('Accept-Language', $response->getVary());

        $response = $this->requestV2('GET', $path . '?locale=en', headers: ['HTTP_ACCEPT_LANGUAGE' => 'es']);
        $this->assertEquals($page->getPageTranslationByLocale('en')->getTitre(), $this->decode($response)['title']);
        $this->assertEquals('en', $response->headers->get('Content-Language'));

        $response = $this->requestV2('GET', $path, headers: ['HTTP_ACCEPT_LANGUAGE' => 'de, es;q=0']);
        $this->assertEquals('fr', $response->headers->get('Content-Language'));

        $this->assertNull($this->requestV2('GET', '/pages/999999999')->headers->get('Content-Language'));
    }

    /**
     * Recherche exacte par slug : au plus une page, en priorité celle de la langue demandée
     * @return void
     */
    public function testCollectionBySlug(): void
    {
        $page = $this->createPageAllDataDefault();
        $other = $this->createPageAllDataDefault();
        $slug = $page->getPageTranslationByLocale('en')->getUrl();
        $other->getPageTranslationByLocale('fr')->setUrl($slug);
        $this->persistAndFlush($other);

        $content = $this->decode($this->requestV2('GET', '/pages?slug=' . $slug . '&locale=en'));
        $this->assertEquals(1, $content['totalItems']);
        $this->assertEquals('/api/v2/pages/' . $page->getId(), $content['member'][0]['@id']);

        $content = $this->decode($this->requestV2('GET', '/pages?slug=' . $slug . '&locale=fr'));
        $this->assertEquals(1, $content['totalItems']);
        $this->assertEquals($other->getId(), $content['member'][0]['id']);

        $this->assertEquals(0, $this->decode($this->requestV2('GET', '/pages?slug=page-inexistante'))['totalItems']);
        $this->assertEquals(0, $this->decode($this->requestV2('GET', '/pages?slug=' . $slug . '&category=blog'))['totalItems']);
        $this->assertEquals(422, $this->requestV2('GET', '/pages?slug=')->getStatusCode());
    }

    /**
     * Page d'accueil seule ou pages hors accueil
     * @return void
     */
    public function testCollectionLanding(): void
    {
        $page = $this->createPageAllDataDefault();
        $page->setLandingPage(true);
        $this->persistAndFlush($page);
        $this->createPageAllDataDefault();

        $content = $this->decode($this->requestV2('GET', '/pages?landing=true'));
        $this->assertEquals(1, $content['totalItems']);
        $this->assertEquals($page->getId(), $content['member'][0]['id']);

        $ids = array_column($this->decode($this->requestV2('GET', '/pages?landing=false&itemsPerPage=100'))['member'], 'id');
        $this->assertNotContains($page->getId(), $ids);

        $this->assertEquals(422, $this->requestV2('GET', '/pages?landing=oui')->getStatusCode());
    }

    /**
     * Page inexistante, au statut brouillon ou désactivée : 404 ; sans token : 401
     * @return void
     */
    public function testNotVisible(): void
    {
        $draft = $this->createPageAllDataDefault();
        $draft->setStatus(PageStatus::DRAFT->value);
        $this->persistAndFlush($draft);
        $disabled = $this->createPageAllDataDefault();
        $disabled->setDisabled(true);
        $this->persistAndFlush($disabled);

        $this->assertProblem($this->requestV2('GET', '/pages/' . $draft->getId()), 404);
        $this->assertProblem($this->requestV2('GET', '/pages/' . $disabled->getId()), 404);
        $slug = $disabled->getPageTranslationByLocale('fr')->getUrl();
        $this->assertEquals(0, $this->decode($this->requestV2('GET', '/pages?slug=' . $slug))['totalItems']);
        $this->assertProblem($this->requestV2('GET', '/pages/999999999'), 404);
        $this->assertProblem($this->requestV2('GET', '/pages/' . $disabled->getId(), self::HEADER_NONE), 401);
    }

    /**
     * POST /page_reads compte une lecture de la page désignée par son IRI
     * @return void
     */
    public function testRead(): void
    {
        $page = $this->createPageAllDataDefault();
        $draft = $this->createPageAllDataDefault();
        $draft->setStatus(PageStatus::DRAFT->value);
        $this->persistAndFlush($draft);
        $id = $page->getId();
        $reads = (int) $page->getPageStatistiqueByKey(PageStatistics::NB_READ->value)->getValue();

        $response = $this->requestV2('POST', '/page_reads', body: ['page' => self::PREFIX . '/pages/' . $id]);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        $this->em->clear();
        $page = $this->em->getRepository(Page::class)->find($id);
        $this->assertEquals($reads + 1, (int) $page->getPageStatistiqueByKey(PageStatistics::NB_READ->value)->getValue());

        $this->assertProblem($this->requestV2('POST', '/page_reads', body: ['page' => self::PREFIX . '/pages/' . $draft->getId()]), 404);
        $this->assertProblem($this->requestV2('POST', '/page_reads', body: ['page' => self::PREFIX . '/pages/999999999']), 404);
        $this->assertProblem($this->requestV2('POST', '/page_reads', body: ['page' => '/api/v2/inconnue/1']), 400);
        $this->assertProblem($this->requestV2('POST', '/page_reads', body: []), 422);
        $this->assertProblem($this->requestV2('POST', '/page_reads', self::HEADER_NONE, ['page' => self::PREFIX . '/pages/' . $id]), 401);
    }

    /**
     * Liste paginée filtrée par id de tag actif et par slug de catégorie
     * @return void
     */
    public function testCollection(): void
    {
        $tag = $this->createTag(customData: ['disabled' => false]);
        $disabledTag = $this->createTag(customData: ['disabled' => true]);
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale]);
            $this->createTagTranslation($disabledTag, ['locale' => $locale]);
        }
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $page = $this->createPageAllDataDefault();
            $this->createPageTag($page, $tag);
            $this->createPageTag($page, $disabledTag);
            $ids[] = $page->getId();
        }
        $tagFilter = '/pages?tag=' . $tag->getId();

        $content = $this->decode($this->requestV2('GET', $tagFilter . '&itemsPerPage=2'));
        $this->assertEquals('Collection', $content['@type']);
        $this->assertEquals(3, $content['totalItems']);
        $this->assertEquals('/api/v2/pages{?locale,category,tag,slug,landing}', $content['search']['template']);
        $this->assertCount(2, $content['member']);
        $this->assertArrayHasKey('next', $content['view']);
        $member = $content['member'][0];
        $this->assertEquals(
            ['@id', '@type', 'id', 'slug', 'title', 'category', 'headerImage', 'author', 'tags', 'createdAt', 'updatedAt'],
            array_keys($member),
        );
        $tagIds = array_column($member['tags'], 'id');
        $this->assertContains($tag->getId(), $tagIds);
        $this->assertNotContains($disabledTag->getId(), $tagIds);

        $content = $this->decode($this->requestV2('GET', $tagFilter . '&itemsPerPage=2&page=2'));
        $this->assertCount(1, $content['member']);
        $this->assertContains($content['member'][0]['id'], $ids);

        $this->assertEquals(3, $this->decode($this->requestV2('GET', $tagFilter . '&category=page'))['totalItems']);
        $this->assertEquals(0, $this->decode($this->requestV2('GET', $tagFilter . '&category=blog'))['totalItems']);
        $this->assertEquals(0, $this->decode($this->requestV2('GET', '/pages?tag=' . $disabledTag->getId()))['totalItems']);

        $this->assertEquals(422, $this->requestV2('GET', '/pages?category=inconnue')->getStatusCode());
        $this->assertEquals(422, $this->requestV2('GET', '/pages?tag=abc')->getStatusCode());
    }
}
