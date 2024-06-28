<?php

namespace App\Service\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MenuService extends AppAdminService
{
    /**
     * Retourne une liste de menu paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $repo = $this->getRepository(Menu::class);
        return $repo->getAllPaginate($page, $limit, $search);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, string $search = null): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();

        $column = [
            $translator->trans('menu.grid.id', domain: 'menu'),
            $translator->trans('menu.grid.name', domain: 'menu'),
            $translator->trans('menu.grid.type', domain: 'menu'),
            $translator->trans('menu.grid.create_at', domain: 'menu'),
            $translator->trans('menu.grid.update_at', domain: 'menu'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $search);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Menu $element */

            $action = $this->generateTabAction($element);

            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $name = $element->getName();

            $data[] = [
                $translator->trans('menu.grid.id', domain: 'menu') => $element->getId() . ' ' . $isDisabled,
                $translator->trans('menu.grid.name', domain: 'menu') => $name,
                $translator->trans('menu.grid.type', domain: 'menu') => $element->getType(),
                $translator->trans('menu.grid.create_at', domain: 'menu') => $element
                    ->getCreatedAt()->format('d/m/y H:i'),
                $translator->trans('menu.grid.update_at', domain: 'menu') => $element
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
     * @param Menu $menu
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Menu $menu): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $optionSystemService = $this->getOptionSystemService();

        $label = $menu->getName();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'url' => $router->generate('admin_menu_update_disabled', ['id' => $menu->getId()]),
            'type' => 'put',
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans('menu.confirm.disabled.msg', ['label' => $label], 'menu')];
        if ($menu->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'type' => 'put',
                'url' => $router->generate('admin_menu_update_disabled', ['id' => $menu->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'type' => 'delete',
                'url' => $router->generate('admin_menu_delete', ['id' => $menu->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('menu.confirm.delete.msg', ['label' =>
                    $label], 'menu')
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
            'id' => $menu->getId(),
            'url' => $router->generate('admin_menu_update', ['id' => $menu->getId()]),
            'ajax' => false];

        return $actions;
    }
}
