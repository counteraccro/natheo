<?php

declare(strict_types=1);
/**
 * Service pour générer la sitemap API
 * @author Gourdon Aymeric
 * @version 1.1
 */

namespace App\Service\Api\Global;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Service\Api\AppApiService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiSitemapService extends AppApiService
{
    /**
     * Génère le sitemap du site
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getSitemap(): array
    {
        /** @var PageRepository $pageRepo */
        $pageRepo = $this->getRepository(Page::class);
        $pages = $pageRepo->findBy(
            ['status' => PageStatus::PUBLISH->value, 'disabled' => false],
            ['updateAt' => \SortDirection::Descending],
        );

        $return = [];
        foreach ($pages as $page) {
            foreach ($page->getPageTranslations() as $pageTranslation) {
                $return[] = [
                    'loc' => $this->getPath($page, $pageTranslation),
                    'priority' => '1.00',
                    'lastmod' => $page->getUpdateAt()->format(DATE_ATOM),
                ];
            }
        }
        return $return;
    }

    /**
     * Pages publiées et actives du sitemap, les plus récemment modifiées en premier
     * @param int $page
     * @param int $limit
     * @return Paginator<Page>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findSitemapPages(int $page, int $limit): Paginator
    {
        /** @var PageRepository $pageRepo */
        $pageRepo = $this->getRepository(Page::class);
        return $pageRepo->getSitemapPagesPaginate($page, $limit);
    }

    /**
     * Chemins publics d'une page, indexés par locale
     * @param Page $page
     * @return array<string, string>
     */
    public function getPaths(Page $page): array
    {
        $paths = [];
        foreach ($page->getPageTranslations() as $pageTranslation) {
            $paths[(string) $pageTranslation->getLocale()] = $this->getPath($page, $pageTranslation);
        }
        return $paths;
    }

    /**
     * Chemin public d'une traduction de page (/<locale>/<catégorie>/<slug>)
     * @param Page $page
     * @param PageTranslation $pageTranslation
     * @return string
     */
    public function getPath(Page $page, PageTranslation $pageTranslation): string
    {
        return '/' .
            $pageTranslation->getLocale() .
            '/' .
            (PageCategory::tryFrom($page->getCategory())?->getSlug() ?? '') .
            '/' .
            $pageTranslation->getUrl();
    }
}
