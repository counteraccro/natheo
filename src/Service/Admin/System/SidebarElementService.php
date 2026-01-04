<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service lier à l'objet SidebarElement
 */

namespace App\Service\Admin\System;

use App\Entity\Admin\System\SidebarElement;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SidebarElementService extends AppAdminService
{
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
                $parent =
                    '<i class="bi ' .
                    $element->getParent()->getIcon() .
                    '"></i> ' .
                    $translator->trans($element->getParent()->getLabel());
            }

            $action = $this->generateTabAction($element);

            $isLock = '';
            if ($element->isLock()) {
                $isLock = '<i class="bi bi-lock-fill"></i>';
            }
            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $data[] = [
                $translator->trans('sidebar.grid.id', domain: 'sidebar') =>
                    $element->getId() . ' ' . $isLock . ' ' . $isDisabled,
                $translator->trans('sidebar.grid.parent', domain: 'sidebar') => $parent,
                $translator->trans('sidebar.grid.label', domain: 'sidebar') =>
                    '<i class="bi ' . $element->getIcon() . '"></i> ' . $translator->trans($element->getLabel()),
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

        $actionDisabled = '';
        if (!$element->isLock()) {
            $actionDisabled = [
                'label' => [
                    'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'url' => $router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]),
                'type' => 'put',
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans(
                    'sidebar.confirm.disabled.msg',
                    [
                        '{label}' =>
                            '<i class="bi ' .
                            $element->getIcon() .
                            '"></i> ' .
                            $translator->trans($element->getLabel()),
                    ],
                    'sidebar',
                ),
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
                ];
            }
        }

        $action = [];
        if ($actionDisabled != '') {
            $action[] = $actionDisabled;
        }

        return $action;
    }
}
