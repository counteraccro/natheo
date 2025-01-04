<?php
/**
 * Service pour la recherche globale
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use App\Entity\Admin\Content\Page\Page;
use App\Utils\Content\Page\PageConst;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

        return $this->formatResult($result, $entity, $search);
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


    /**
     * @param Paginator $paginator
     * @param string $entity
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatResult(Paginator $paginator, string $entity, string $search): array
    {
        $return = ['elements' => [], 'total' => $paginator->count()];
        $locales = $this->getLocales();
        foreach ($paginator as $item) {
            switch ($entity) {
                case Page::class:
                    /** @var Page $item */
                    $return['elements'][] = $this->formatResulPage($item, $locales['current'], $search);
                    break;
                default:
            }
        }

        return $return;
    }

    /**
     * @param Page $page
     * @param string $locale
     * @param string $search
     * @return array
     */
    private function formatResulPage(Page $page, string $locale, string $search): array
    {
        $label = $page->getPageTranslationByLocale($locale)->getTitre();
        $label = $this->highlightText($search, $label);

        $content = [];
        foreach ($page->getPageContents() as $pageContent) {
            if ($pageContent->getType() === PageConst::CONTENT_TYPE_TEXT) {
                $text = $pageContent->getPageContentTranslationByLocale($locale)->getText();
                $re = '/\b((?:\w+[^\w\n]+){0,2}' . $search . '\b(?:[^\w\n]+\w+){0,2})/mu';
                preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);

                foreach($matches as $matche)
                {
                    $content[] = $this->highlightText($search, $matche[0]);
                }

                /*if (str_contains($text, $search)) {
                    $start = strpos($text, $search) - 25;
                    $end = strlen($search) + 25;
                    $text = substr($text, $start, $end);
                    $content[] = $this->highlightText($search, $text);
                }*/
            }
        }

        $return = [
            'id' => $page->getId(),
            'label' => $label,
            'contents' => $content,
        ];
        return $return;
    }

    /**
     * Surligne un élement de recherche
     * @param string $search
     * @param string $text
     * @return string
     */
    private function highlightText(string $search, string $text): string
    {
        return str_replace($search, '<mark>' . $search . '</mark>', $text);
    }
}
