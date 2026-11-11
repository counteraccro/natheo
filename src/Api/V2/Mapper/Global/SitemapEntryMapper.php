<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Convertit une page publiée en ressource SitemapEntry de l'API v2
 */

namespace App\Api\V2\Mapper\Global;

use App\Api\V2\Output\Global\SitemapAlternate;
use App\Api\V2\Resource\Content\Page;
use App\Api\V2\Resource\Global\SitemapEntry;
use App\Entity\Admin\Content\Page\Page as PageEntity;

final readonly class SitemapEntryMapper
{
    /**
     * @param PageEntity $entity
     * @param array<string, string> $paths chemins publics de la page, indexés par locale
     * @return SitemapEntry
     */
    public function mapSummary(PageEntity $entity, array $paths): SitemapEntry
    {
        $page = new Page();
        $page->id = (int) $entity->getId();

        $entry = new SitemapEntry();
        $entry->page = $page;
        $entry->updatedAt =
            $entity->getUpdateAt() === null ? null : \DateTimeImmutable::createFromInterface($entity->getUpdateAt());
        foreach ($paths as $locale => $path) {
            $entry->alternates[] = new SitemapAlternate((string) $locale, $path);
        }
        return $entry;
    }
}
