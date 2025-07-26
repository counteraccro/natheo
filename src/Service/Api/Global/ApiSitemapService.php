<?php
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Api\Global;

use App\Entity\Admin\Content\Page\Page;
use App\Service\Api\AppApiService;
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

        $pageRepo = $this->getRepository(Page::class);

        $return = [];

        /*
         * $urls[] = [
                'loc' => $this->generateUrl('post_show', [
                    'slug' => $post->getSlug(),
                ]),
                'priority' => '1.00',
                'lastmod' => $post->getUpdatedAt()->format('Y-m-d')
            ];
         */

        $return[] = [
            'loc' => 'aaaa',
            'priority' => '1.0',
            'lastmod' => (new \DateTime())->format('Y-m-d')
        ];

        return $return;
    }
}