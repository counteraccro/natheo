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
            $this->translator->trans('sidebar.grid.action'),
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach($dataPaginate as $element)
        {
            /* @var SidebarElement $element */

            $data[] = [
                $this->translator->trans('sidebar.grid.id') => $element->getId(),
                $this->translator->trans('sidebar.grid.parent') => 'Parent',
                $this->translator->trans('sidebar.grid.label') => '<i class="bi ' . $element->getIcon() . '"></i> ' . $this->translator->trans($element->getLabel()),
                $this->translator->trans('sidebar.grid.role') => $element->getRole(),
                $this->translator->trans('sidebar.grid.description') => $this->translator->trans($element->getDescription()),
                $this->translator->trans('sidebar.grid.created_at') => 'Date 1',
                $this->translator->trans('sidebar.grid.update_at') => 'Date 2',
                $this->translator->trans('sidebar.grid.action') => 'Action',
            ];
        }

        return [
            'nb' => $nb,
            'data' => $data,
            'column' => $column
        ];

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