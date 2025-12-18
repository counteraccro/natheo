<?php
/**
 * @author Gourdon Aymeric
 * @version 1.3
 * Service gérant les tags de l'application
 */

namespace App\Service\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Repository\Admin\Content\Tag\TagRepository;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Utils\Content\Tag\TagRender;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TagService extends AppAdminService
{
    /**
     * Retourne une liste de tag paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $repo = $this->getRepository(Tag::class);
        return $repo->getAllPaginate($page, $limit, $queryParams);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();
        $requestStack = $this->getRequestStack();

        $column = [
            $translator->trans('tag.grid.id', domain: 'tag'),
            $translator->trans('tag.grid.label', domain: 'tag'),
            $translator->trans('tag.grid.color', domain: 'tag'),
            $translator->trans('tag.grid.created_at', domain: 'tag'),
            $translator->trans('tag.grid.update_at', domain: 'tag'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Tag $element */

            $action = $this->generateTabAction($element);
            $locale = $requestStack->getCurrentRequest()->getLocale();
            $tagRender = new TagRender($element, $locale);

            $data[] = [
                $translator->trans('tag.grid.id', domain: 'tag') => $element->getId(),
                $translator->trans('tag.grid.label', domain: 'tag') => $tagRender->getHtml(),
                $translator->trans('tag.grid.color', domain: 'tag') => $element->getColor(),
                $translator->trans('tag.grid.created_at', domain: 'tag') => $element
                    ->getCreatedAt()
                    ->format('d/m/y H:i'),
                $translator->trans('tag.grid.update_at', domain: 'tag') => $element->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $action,
                'isDisabled' => $element->isDisabled(),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('tag.grid.id', domain: 'tag'),
                'label' => $translator->trans('tag.grid.label', domain: 'tag'),
                'color' => $translator->trans('tag.grid.color', domain: 'tag'),
                'createdAt' => $translator->trans('tag.grid.created_at', domain: 'tag'),
                'updateAt' => $translator->trans('tag.grid.update_at', domain: 'tag'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Génère le tableau d'action pour le Grid des sidebarElement
     * @param Tag $tag
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Tag $tag): array
    {
        $translator = $this->getTranslator();
        $requestStack = $this->getRequestStack();
        $router = $this->getRouter();
        $optionSystemService = $this->getOptionSystemService();

        $label = $tag->getTagTranslationByLocale($requestStack->getCurrentRequest()->getLocale())->getLabel();

        $actionDisabled = [
            'label' => [
                'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
            ],
            'color' => 'primary',
            'type' => 'put',
            'url' => $router->generate('admin_tag_update_disabled', ['id' => $tag->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans('tag.confirm.disabled.msg', ['label' => $label], 'tag'),
        ];
        if ($tag->isDisabled()) {
            $actionDisabled = [
                'label' => [
                    'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                    'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'type' => 'put',
                'url' => $router->generate('admin_tag_update_disabled', ['id' => $tag->getId()]),
                'ajax' => true,
            ];
        }

        $actionDelete = '';
        if ($optionSystemService->canDelete()) {
            $actionDelete = [
                'label' => [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ],
                'type' => 'delete',
                'color' => 'danger',
                'url' => $router->generate('admin_tag_delete', ['id' => $tag->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('tag.confirm.delete.msg', ['label' => $label], 'tag'),
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        // Bouton edit
        $actions[] = [
            'label' => [
                'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
            ],
            'color' => 'primary',
            'type' => 'post',
            'id' => $tag->getId(),
            'url' => $router->generate('admin_tag_update', ['id' => $tag->getId()]),
            'ajax' => false,
        ];

        return $actions;
    }

    /**
     * Recherche une liste de tag en fonction de la locale
     * @param string $locale
     * @param string $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchByLocale(string $locale, string $search): array
    {
        /** @var TagRepository $repo */
        $repo = $this->getRepository(Tag::class);
        $result = $repo->searchByName($locale, $search);

        $return = [];
        foreach ($result as $row) {
            $return[$row['id']] = [
                'label' =>
                    '<span class="me-1 badge rounded-pill badge-nat no-control" 
            style="background-color: ' .
                    $row['color'] .
                    ';">' .
                    $row['label'] .
                    '</span>',
                'data' => $row['label'],
            ];
        }
        return $return;
    }

    /**
     * Retourne un tag en fonction de son label et de la locale
     * Si le tag n'existe pas retourne null
     * @param string $locale
     * @param string $label
     * @return ?Tag
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function searchByNameByLocale(string $locale, string $label): ?Tag
    {
        $repo = $this->getRepository(TagTranslation::class);
        /** @var TagTranslation $tagTranslation */
        $tagTranslation = $repo->findOneBy(['label' => $label, 'locale' => $locale]);

        return $tagTranslation?->getTag();
    }

    /**
     * Créer un tag en fonction d'un label et de la locale
     * @param string $locale
     * @param string $label
     * @return Tag|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function newTagByNameAndLocale(string $locale, string $label): ?Tag
    {
        $tag = $this->searchByNameByLocale($locale, $label);
        if ($tag !== null) {
            if ($tag->isDisabled()) {
                return null;
            }
            return $tag;
        }

        $tag = new Tag();
        $tag->setColor('#' . str_pad(dechex(mt_rand(0, 0xffffff)), 6, '0', STR_PAD_LEFT));

        $locales = $this->getLocales();
        foreach ($locales['locales'] as $loc) {
            $tagTranslation = new TagTranslation();
            $strLabel = $label;
            if ($loc !== $locale) {
                $strLabel .= ' (' . $loc . ')';
            }
            $tagTranslation->setLocale($loc);
            $tagTranslation->setLabel($strLabel);
            $tagTranslation->setTag($tag);
            $tag->addTagTranslation($tagTranslation);
        }
        $this->save($tag);

        return $tag;
    }
}
