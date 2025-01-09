<?php
/**
 * Service pour la recherche globale
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use App\Entity\Admin\Content\Page\Page;
use App\Utils\Content\Page\PageConst;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\System\User\PersonalData;
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
        $result = $repository->search($search, $this->getLocales()['current'], $page, $limit);

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatResulPage(Page $page, string $locale, string $search): array
    {
        $label = $page->getPageTranslationByLocale($locale)->getTitre();
        $label = $this->highlightText($search, $label);

        $content = [];
        foreach ($page->getPageContents() as $pageContent) {
            if ($pageContent->getType() === PageConst::CONTENT_TYPE_TEXT) {
                $text = $pageContent->getPageContentTranslationByLocale($locale)->getText();
                $re = '/(\B||\b)((?-i:\w+[^\w\n]+){0,10}' . $search . '(\B||\b)(?-i:[^\w\n]+\w+){0,10})/mu';
                preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);

                foreach($matches as $matche)
                {
                    $content[] = $this->highlightText($search, $matche[0]);
                }
            }
        }

        $router = $this->getRouter();
        $personalData = new PersonalData($page->getUser(),
            $page->getUser()->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER)->getValue());

        return [
            'id' => $page->getId(),
            'label' => $label,
            'contents' => $content,
            'date' => [
                'create' =>  $page->getCreatedAt()->format('d/m/y H:i'),
                'update' =>  $page->getUpdateAt()->format('d/m/y H:i')
            ],
            'author' => $this->highlightText($search, $personalData->getPersonalData()),
            'urls' => [
                'edit' => $router->generate('admin_page_update', ['id' => $page->getId()]),
                'preview' => $router->generate('admin_page_preview', ['id' => $page->getId(), 'locale' => $locale]),
            ]
        ];
    }

    /**
     * Surligne un élement de recherche
     * @param string $search
     * @param string $text
     * @return string
     */
    private function highlightText(string $search, string $text): string
    {
        return str_ireplace($search, '<mark>' . $search . '</mark>', $text);
    }
}
