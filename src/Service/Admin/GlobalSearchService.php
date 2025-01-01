<?php
/**
 * Service pour la recherche globale
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use App\Entity\Admin\Content\Page\Page;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GlobalSearchService extends AppAdminService
{
    /**
     * Recherche globale
     * @param string $entity
     * @param string $search
     * @param int $page
     * @param int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function globalSearch(string $entity, string $search, int $page, int $limit): array
    {
        $translate = $this->getTranslator();

        $entity = $this->getEntityByString($entity);
        if ($entity === "") {
            return ['error' => $translate->trans('global_search.error.notFound', domain: 'global_search')];
        }

        $repository = $this->getRepository(ucfirst($entity));
        $result = $repository->search($search, $page, $limit);

        return [$result->count()];
    }

    /**
     * Retourne
     * @param string $entity
     * @return string
     */
    private function getEntityByString(string $entity): string
    {
        return match ($entity) {
            'page' => Page::class,
            default => '',
        };
    }
}
