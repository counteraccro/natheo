<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service lier à l'objet SidebarElement
 */

namespace App\Service\Admin;

use App\Entity\Admin\SidebarElement;
use App\Utils\Debug;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SidebarElementService extends AppAdminService
{
    /**
     * Récupère l'ensemble des sidebarElement parent
     * @param bool $disabled
     * @return mixed
     */
    public function getAllParent(bool $disabled = false): mixed
    {
        $sidebarElementRepo = $this->getSidebarElementRepo();
        return $sidebarElementRepo->getAllParent($disabled);
    }

    /**
     * Retourne une liste de sidebarElement paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $sidebarElementRepo = $this->getSidebarElementRepo();
        return $sidebarElementRepo->getAllPaginate($page, $limit);
    }

    public function getAllFormatToGrid(int $page, int $limit)
    {
        $column = [
            $this->translator->trans('sidebar.grid.id'),
            $this->translator->trans('sidebar.grid.parent'),
            $this->translator->trans('sidebar.grid.label'),
            $this->translator->trans('sidebar.grid.role'),
            $this->translator->trans('sidebar.grid.description'),
            $this->translator->trans('sidebar.grid.created_at'),
            $this->translator->trans('sidebar.grid.update_at'),
            'action',
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var SidebarElement $element */

            $parent = '---';
            if ($element->getParent() !== null) {
                $parent = '<i class="bi ' . $element->getParent()->getIcon() . '"></i> ' . $this->translator->trans($element->getParent()->getLabel());
            }

            $action = $this->generateTabAction($element);

            $is_lock = '';
            if($element->isLock())
            {
                $is_lock = '<i class="bi bi-lock-fill"></i>';
            }


            $data[] = [
                $this->translator->trans('sidebar.grid.id') => $element->getId(),
                $this->translator->trans('sidebar.grid.parent') => $parent,
                $this->translator->trans('sidebar.grid.label') => $is_lock . '<i class="bi ' . $element->getIcon() . '"></i> ' . $this->translator->trans($element->getLabel()),
                $this->translator->trans('sidebar.grid.role') => $element->getRole(),
                $this->translator->trans('sidebar.grid.description') => $this->translator->trans($element->getDescription()),
                $this->translator->trans('sidebar.grid.created_at') => $element->getCreatedAt()->format('d/m/y H:i'),
                $this->translator->trans('sidebar.grid.update_at') => $element->getUpdateAt()->format('d/m/y H:i'),
                'action' => json_encode($action),
            ];
        }

        return [
            'nb' => $nb,
            'data' => $data,
            'column' => $column,
            'listLimit' => $this->getOptionsSelectLimit()
        ];

    }

    /**
     * Génère le tableau d'action pour le Grid des sidebarElement
     * @param SidebarElement $element
     * @return array[]|string[]
     */
    private function generateTabAction(SidebarElement $element): array
    {
        $action_disabled = '';
        if (!$element->isLock()) {
            $action_disabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>', 'id' => $element->getId(), 'url' => $this->router->generate('admin_dashboard_index'), 'ajax' => true];
            if ($element->isDisabled()) {
                $action_disabled = ['label' => '<i class="bi bi-eye-fill"></i>', 'id' => $element->getId(), 'url' => $this->router->generate('admin_dashboard_index'), 'ajax' => true];
            }
        }

        $action = [
            ['label' => '<i class="bi bi-list-ul"></i>', 'id' => $element->getId(), 'url' => $this->router->generate('admin_dashboard_index'), 'ajax' => false]
        ];

        if($action_disabled != '')
        {
            $action[] = $action_disabled;
        }

        return $action;
    }

    /**
     * Retourne le repository Sidebar
     * @return EntityRepository
     */
    private function getSidebarElementRepo(): EntityRepository
    {
        return $this->entityManager->getRepository(SidebarElement::class);
    }
}