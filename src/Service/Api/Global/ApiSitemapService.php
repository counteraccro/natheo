<?php
/**
 * Service pour générer la sitemap API
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Api\Global;

use App\Entity\Admin\Content\Page\Page;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Service\Api\AppApiService;
use App\Utils\Content\Page\PageConst;
use App\Utils\System\Options\OptionSystemKey;
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
        $pages = $pageRepo->findBy(['status' => PageConst::STATUS_PUBLISH], ['updateAt' => 'DESC']);
        $pageService = $this->getPageService();

        $return = [];
        foreach ($pages as $page) {
            foreach ($page->getPageTranslations() as $pageTranslation) {
                $url =
                    '/' .
                    $pageTranslation->getLocale() .
                    '/' .
                    strtolower($pageService->getCategoryById($page->getCategory())) .
                    '/' .
                    $pageTranslation->getUrl();
                $return[] = [
                    'loc' => $url,
                    'priority' => '1.00',
                    'lastmod' => $page->getUpdateAt()->format(DATE_ATOM),
                ];
            }
        }
        return $return;
    }
}
