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
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $repo = $this->getRepository(SidebarElement::class);
        return $repo->getAllPaginate($page, $limit);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit): array
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

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var SidebarElement $element */

            $parent = '---';
            if ($element->getParent() !== null) {
                $parent = '<i class="bi ' . $element->getParent()->getIcon() . '"></i> ' .
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
                $translator->trans('sidebar.grid.id', domain: 'sidebar') => $element->getId() . ' ' . $isLock .
                    ' ' . $isDisabled,
                $translator->trans('sidebar.grid.parent', domain: 'sidebar') => $parent,
                $translator->trans('sidebar.grid.label', domain: 'sidebar') => '<i class="bi ' .
                    $element->getIcon() . '"></i> ' . $translator->trans($element->getLabel()),
                $translator->trans('sidebar.grid.role', domain: 'sidebar') => $gridService
                    ->renderRole($element->getRole()),
                $translator->trans('sidebar.grid.description', domain: 'sidebar') => $translator
                    ->trans($element->getDescription()),
                $translator->trans('sidebar.grid.created_at', domain: 'sidebar') => $element
                    ->getCreatedAt()->format('d/m/y H:i'),
                $translator->trans('sidebar.grid.update_at', domain: 'sidebar') => $element
                    ->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $action,
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate)
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
            $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
                'url' => $router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]),
                'type' => 'put',
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('sidebar.confirm.disabled.msg', ['{label}' => '<i class="bi ' .
                    $element->getIcon() . '"></i> ' . $translator->trans($element->getLabel())], 'sidebar')];
            if ($element->isDisabled()) {
                $actionDisabled = [
                    'label' => '<i class="bi bi-eye-fill"></i>',
                    'type' => 'put',
                    'url' => $router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]),
                    'ajax' => true
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
