<?php

declare(strict_types=1);

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service lier à l'objet SidebarElement
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\SidebarElement;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Enum\Admin\Global\SvgIcon;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SidebarElementService extends AppAdminService
{
    /**
     * Identifiant du jeton CSRF pour masquer / afficher un sidebarElement
     */
    public const string CSRF_TOKEN_UPDATE_DISABLED = 'sidebar_update_disabled';

    /**
     * Récupère l'ensemble des sidebarElement parent
     * @param bool $disabled
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllParent(bool $disabled = false): mixed
    {
        $repo = $this->getRepository(SidebarElement::class);
        return $repo->getAllParent($disabled);
    }

    /**
     * Retourne une liste de sidebarElement paginé
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $repo = $this->getRepository(SidebarElement::class);
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

        $column = [
            $translator->trans('sidebar.grid.id', domain: 'sidebar'),
            $translator->trans('sidebar.grid.parent', domain: 'sidebar'),
            $translator->trans('sidebar.grid.label', domain: 'sidebar'),
            $translator->trans('sidebar.grid.role', domain: 'sidebar'),
            $translator->trans('sidebar.grid.description', domain: 'sidebar'),
            $translator->trans('sidebar.grid.created_at', domain: 'sidebar'),
            $translator->trans('sidebar.grid.update_at', domain: 'sidebar'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var SidebarElement $element */

            $parent = '---';
            if ($element->getParent() !== null) {
                $parent = $this->getLabelWithIcon($element->getParent());
            }
            $icon = $this->getLabelWithIcon($element);

            $action = $this->generateTabAction($element);

            $isLock = '';
            if ($element->isLock()) {
                $isLock = SvgIcon::LOCK->render('inline w-4 h-4');
            }
            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = SvgIcon::EYE_SLASH->render('inline w-4 h-4');
            }

            $data[] = [
                $translator->trans('sidebar.grid.id', domain: 'sidebar') =>
                    $element->getId() . ' ' . $isLock . ' ' . $isDisabled,
                $translator->trans('sidebar.grid.parent', domain: 'sidebar') => $parent,
                $translator->trans('sidebar.grid.label', domain: 'sidebar') => $icon,
                $translator->trans('sidebar.grid.role', domain: 'sidebar') => $gridService->renderRole(
                    $element->getRole(),
                ),
                $translator->trans('sidebar.grid.description', domain: 'sidebar') => $translator->trans(
                    $element->getDescription(),
                ),
                $translator->trans('sidebar.grid.created_at', domain: 'sidebar') => $element
                    ->getCreatedAt()
                    ->format('d/m/y H:i'),
                $translator->trans('sidebar.grid.update_at', domain: 'sidebar') => $element
                    ->getUpdateAt()
                    ->format('d/m/y H:i'),
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
                'id' => $translator->trans('sidebar.grid.id', domain: 'sidebar'),
                'label' => $translator->trans('sidebar.grid.label', domain: 'sidebar'),
                'createdAt' => $translator->trans('sidebar.grid.created_at', domain: 'sidebar'),
                'updateAt' => $translator->trans('sidebar.grid.update_at', domain: 'sidebar'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Génère le tableau d'action pour le Grid des sidebarElement
     * @param SidebarElement $element
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(SidebarElement $element): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $csrfToken = $this->getCsrfTokenManager()->getToken(self::CSRF_TOKEN_UPDATE_DISABLED)->getValue();

        $actionDisabled = '';
        if (!$element->isLock()) {
            $actionDisabled = [
                'label' => [SvgIcon::EYE_SLASH->value],
                'color' => 'primary',
                'url' => $router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]),
                'type' => 'put',
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans(
                    'sidebar.confirm.disabled.msg',
                    ['label' => $this->getLabelWithIcon($element)],
                    'sidebar',
                ),
                'csrf' => $csrfToken,
            ];
            if ($element->isDisabled()) {
                $actionDisabled = [
                    'label' => [
                        'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                        'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                    ],
                    'color' => 'primary',
                    'type' => 'put',
                    'url' => $router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]),
                    'ajax' => true,
                    'csrf' => $csrfToken,
                ];
            }
        }

        $action = [];
        if ($actionDisabled != '') {
            $action[] = $actionDisabled;
        }

        return $action;
    }

    /**
     * Retourne le label traduit d'un sidebarElement précédé de son icône
     * @param SidebarElement $element
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLabelWithIcon(SidebarElement $element): string
    {
        return '<span class="inline-flex items-center">' .
            SvgIcon::renderPath($element->getIcon(), 'w-4 h-4 mr-2') .
            htmlspecialchars($this->getTranslator()->trans($element->getLabel())) .
            '</span>';
    }
}
