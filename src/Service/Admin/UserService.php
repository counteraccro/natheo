<?php

namespace App\Service\Admin;

use App\Entity\Admin\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserService extends AppAdminService
{

    /**
     * Retourne une liste de sidebarElement paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $repo = $this->getRepository(User::class);
        return $repo->getAllPaginate($page, $limit);
    }

    /**
     * Construit le tableau de donnée à envoyer au tableau GRID
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAllFormatToGrid(int $page, int $limit)
    {
        /*$column = [
            $this->translator->trans('sidebar.grid.id'),
            $this->translator->trans('sidebar.grid.parent'),
            $this->translator->trans('sidebar.grid.label'),
            $this->translator->trans('sidebar.grid.role'),
            $this->translator->trans('sidebar.grid.description'),
            $this->translator->trans('sidebar.grid.created_at'),
            $this->translator->trans('sidebar.grid.update_at'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var SidebarElement $element

            $parent = '---';
            if ($element->getParent() !== null) {
                $parent = '<i class="bi ' . $element->getParent()->getIcon() . '"></i> ' . $this->translator->trans($element->getParent()->getLabel());
            }

            $action = $this->generateTabAction($element);

            $is_lock = '';
            if ($element->isLock()) {
                $is_lock = '<i class="bi bi-lock-fill"></i>';
            }

            $data[] = [
                $this->translator->trans('sidebar.grid.id') => $element->getId() . ' ' . $is_lock,
                $this->translator->trans('sidebar.grid.parent') => $parent,
                $this->translator->trans('sidebar.grid.label') => '<i class="bi ' . $element->getIcon() . '"></i> ' . $this->translator->trans($element->getLabel()),
                $this->translator->trans('sidebar.grid.role') => $element->getRole(),
                $this->translator->trans('sidebar.grid.description') => $this->translator->trans($element->getDescription()),
                $this->translator->trans('sidebar.grid.created_at') => $element->getCreatedAt()->format('d/m/y H:i'),
                $this->translator->trans('sidebar.grid.update_at') => $element->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => json_encode($action),
            ];
        }

        $tabReturn = [
            'nb' => $nb,
            'data' => $data,
            'column' => $column,
        ];
        return $this->gridService->addAllDataRequiredGrid($tabReturn);*/

    }

    /**
     * Génère le tableau d'action pour le Grid des sidebarElement
     * @param User $user
     * @return array[]|string[]
     */
    private function generateTabAction(User $user): array
    {
       /* $action_disabled = '';
        if (!$element->isLock()) {
            $action_disabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
                'id' => $element->getId(),
                'url' => $this->router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $this->translator->trans('sidebar.confirm.disabled.msg', ['{label}' => '<i class="bi ' . $element->getIcon() . '"></i> ' . $this->translator->trans($element->getLabel())])];
            if ($element->isDisabled()) {
                $action_disabled = ['label' => '<i class="bi bi-eye-fill"></i>', 'id' => $element->getId(), 'url' => $this->router->generate('admin_sidebar_update_disabled', ['id' => $element->getId()]), 'ajax' => true];
            }
        }

        $action = [];
        if ($action_disabled != '') {
            $action[] = $action_disabled;
        }

        return $action;*/
    }

    /**
     * Permet de sauvegarder un user en base de donnée
     * @param User $user
     * @param bool $flush
     * @return void
     */
    public function save(User $sidebarElement, bool $flush = true)
    {
        $repo = $this->getRepository(User::class);
        $repo->save($sidebarElement, $flush);
    }
}