<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource SitemapEntry de l'API v2
 */

namespace App\Tests\Api\V2\Global;

use App\Api\V2\Resource\Global\SitemapEntry;
use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Tests\Api\V2\AppApiV2TestCase;

class SitemapTest extends AppApiV2TestCase
{
    /**
     * Test GET /api/v2/sitemap
     * @return void
     */
    public function testGetSitemap(): void
    {
        $this->assertProblem($this->requestV2('GET', '/sitemap', self::HEADER_NONE), 401);

        $this->createPageAllDataDefault();
        $page = $this->createPageAllDataDefault();
        // Catégorie accentuée : le chemin utilise le slug ASCII attendu par le front
        $page->setCategory(PageCategory::EVENEMENT->value);
        $this->persistAndFlush($page);
        $this->setUpdateAt($page, '+1 day');

        $disabled = $this->createPageAllDataDefault();
        $disabled->setDisabled(true);
        $this->persistAndFlush($disabled);
        $this->setUpdateAt($disabled, '+2 days');

        $draft = $this->createPageAllDataDefault();
        $draft->setStatus(PageStatus::DRAFT->value);
        $this->persistAndFlush($draft);
        $this->setUpdateAt($draft, '+2 days');

        $content = $this->decode($this->requestV2('GET', '/sitemap?itemsPerPage=1'));
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceCollectionJsonSchema(SitemapEntry::class);
        $this->assertArrayHasKey('next', $content['view']);
        $this->assertCount(1, $content['member']);

        $entry = $content['member'][0];
        $this->assertEquals('/api/v2/pages/' . $page->getId(), $entry['page']);
        $this->assertEquals($page->getUpdateAt()->format(DATE_ATOM), $entry['updatedAt']);
        $this->assertArrayNotHasKey('priority', $entry);
        $this->assertCount($page->getPageTranslations()->count(), $entry['alternates']);
        $alternates = array_column($entry['alternates'], 'path', 'locale');
        foreach ($page->getPageTranslations() as $translation) {
            $this->assertEquals(
                '/' . $translation->getLocale() . '/evenement/' . $translation->getUrl(),
                $alternates[$translation->getLocale()],
            );
        }

        $content = $this->decode($this->requestV2('GET', '/sitemap?itemsPerPage=100'));
        $pages = array_column($content['member'], 'page');
        $this->assertNotContains('/api/v2/pages/' . $disabled->getId(), $pages);
        $this->assertNotContains('/api/v2/pages/' . $draft->getId(), $pages);
        $this->assertCount(
            $this->em->getRepository(Page::class)->count([
                'status' => PageStatus::PUBLISH->value,
                'disabled' => false,
            ]),
            $pages,
        );
    }

    /**
     * Fixe la date de modification d'une page, sans passer par le callback PreUpdate
     * @param Page $page
     * @param string $modifier
     * @return void
     */
    private function setUpdateAt(Page $page, string $modifier): void
    {
        $this->em
            ->createQuery('UPDATE ' . Page::class . ' p SET p.updateAt = :date WHERE p.id = :id')
            ->setParameters(['date' => new \DateTime($modifier), 'id' => $page->getId()])
            ->execute();
        $this->em->refresh($page);
    }
}
