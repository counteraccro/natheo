<?php

namespace App\Service\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Repository\Admin\Content\Menu\MenuElementRepository;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Utils\Content\Menu\MenuConst;
use App\Utils\Content\Menu\MenuFactory;
use App\Utils\Global\OrderEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
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
            $listeType = $this->getListType();
            $listePosition = $this->getListPosition();
            $strType = $listePosition[$element->getPosition()] . ' / ' . $listeType[$element->getPosition()][$element->getType()];

            $data[] = [
                $translator->trans('menu.grid.id', domain: 'menu') => $element->getId() . ' ' . $isDisabled,
                $translator->trans('menu.grid.name', domain: 'menu') => $name,
                $translator->trans('menu.grid.type', domain: 'menu') => $strType,
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

    /**
     * Retourne une liste des positions
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListPosition(): array
    {
        $translator = $this->getTranslator();

        return [
            MenuConst::POSITION_HEADER => $translator->trans('menu.position.header', domain: 'menu'),
            MenuConst::POSITION_RIGHT => $translator->trans('menu.position.right', domain: 'menu'),
            MenuConst::POSITION_FOOTER => $translator->trans('menu.position.footer', domain: 'menu'),
            MenuConst::POSITION_LEFT => $translator->trans('menu.position.left', domain: 'menu')
        ];
    }

    /**
     * Retourne une liste de type de menu
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListType()
    {
        $translator = $this->getTranslator();
        return [
            MenuConst::POSITION_HEADER => [
                MenuConst::TYPE_HEADER_SIDE_BAR => $translator->trans('menu.header.type.side-bar', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT => $translator->trans('menu.header.type.deroulant', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT_1_LIGNE_1_COLONNE => $translator->trans('menu.header.type.deroulant.1ligne.1col', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT_1_LIGNE_2_COLONNES => $translator->trans('menu.header.type.deroulant.1ligne.2col', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT_1_LIGNE_3_COLONNES => $translator->trans('menu.header.type.deroulant.1ligne.3col', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT_1_LIGNE_4_COLONNES => $translator->trans('menu.header.type.deroulant.1ligne.4col', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT_2_LIGNES_1_COLONNE => $translator->trans('menu.header.type.deroulant.2lignes.1col', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT_2_LIGNES_2_COLONNES => $translator->trans('menu.header.type.deroulant.2lignes.2col', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT_2_LIGNES_3_COLONNES => $translator->trans('menu.header.type.deroulant.2lignes.3col', domain: 'menu'),
                MenuConst::TYPE_HEADER_MENU_DEROULANT_2_LIGNES_4_COLONNES => $translator->trans('menu.header.type.deroulant.2lignes.4col', domain: 'menu'),
            ],
            MenuConst::POSITION_LEFT => [
                MenuConst::TYPE_LEFT_RIGHT_SIDE_BAR => $translator->trans('menu.left.right.type.side-bar', domain: 'menu'),
                MenuConst::TYPE_LEFT_RIGHT_SIDE_BAR_ACCORDEON => $translator->trans('menu.left.right.type.side-bar.accordeon', domain: 'menu'),
            ],
            MenuConst::POSITION_RIGHT => [
                MenuConst::TYPE_LEFT_RIGHT_SIDE_BAR => $translator->trans('menu.left.right.type.side-bar', domain: 'menu'),
                MenuConst::TYPE_LEFT_RIGHT_SIDE_BAR_ACCORDEON => $translator->trans('menu.left.right.type.side-bar.accordeon', domain: 'menu'),
            ],
            MenuConst::POSITION_FOOTER => [
                MenuConst::TYPE_FOOTER_1_COLONNE => $translator->trans('menu.footer.type.col1', domain: 'menu'),
                MenuConst::TYPE_FOOTER_2_COLONNES => $translator->trans('menu.footer.type.col2', domain: 'menu'),
                MenuConst::TYPE_FOOTER_3_COLONNES => $translator->trans('menu.footer.type.col3', domain: 'menu'),
                MenuConst::TYPE_FOOTER_4_COLONNES => $translator->trans('menu.footer.type.col4', domain: 'menu'),
            ]
        ];
    }

    /**
     * Ajoute un menuElement au menu et le sauvegarde, retourne son id
     * @param int $idMenu
     * @param int $columnP
     * @param int $rowP
     * @param int|null $idParent
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addMenuElement(int $idMenu, int $columnP, int $rowP, int $idParent = null): int
    {
        $menuFactory = new MenuFactory($this->getLocales()['locales']);
        $menuElement = $menuFactory->createMenuElement();
        $menuElement->setColumnPosition($columnP);
        $menuElement->setRowPosition($rowP);
        $menuElement->setLinkTarget(MenuConst::LINK_TARGET_SELF);

        /** @var Menu $menu */
        $menu = $this->findOneById(Menu::class, $idMenu);
        $menuElement->setMenu($menu);

        if ($idParent === null || $idParent === 0) {
            $menu->addMenuElement($menuElement);
            $this->save($menu);
        } else {
            /** @var MenuElement $parent */
            $parent = $this->findOneById(MenuElement::class, $idParent);
            $parent->addChild($menuElement);
            $this->save($menuElement);
        }

        return $menuElement->getId();
    }

    /**
     * Met à jour le parent d'un menuElement
     * @param int $idElement
     * @param int $columnP
     * @param int $rowP
     * @param int $newIdParent
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateParent(int $idElement, int $columnP, int $rowP, int $newIdParent = 0): void
    {
        $menuElement = $this->findOneById(MenuElement::class, $idElement);
        if ($newIdParent === 0) {
            $menuElement->setParent(null);
        } else {
            $parent = $this->findOneById(MenuElement::class, $newIdParent);
            $menuElement->setParent($parent);
        }
        $menuElement->setColumnPosition($columnP);
        $menuElement->setRowPosition($rowP);

        $this->save($menuElement);
        $elements = $this->getMenuElementByMenuAndParent($menuElement->getMenu()->getId());
        $this->regenerateColumnAndRowPosition($elements);
    }

    /**
     * Reconstruit les positions de column et row en fonction du menu
     * @param array $menuElements
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function regenerateColumnAndRowPosition(array $menuElements): void
    {
        $columnRef = null;
        $rowRef = 0;
        foreach ($menuElements as $menuElement) {
            /** @var MenuElement $menuElement */

            if ($columnRef === null || $columnRef != $menuElement->getColumnPosition()) {
                $columnRef++;
                $rowRef = 1;
            }

            $menuElement->setColumnPosition($columnRef);
            $menuElement->setRowPosition($rowRef++);
            if (!$menuElement->getChildren()->isEmpty()) {
                $this->regenerateColumnAndRowPosition($menuElement->getChildren()->toArray());
            }
            $this->save($menuElement);
        }
    }

    /**
     * Retourne une liste de menuElement en fonction d'un Menu et de son parent. Si parent = null, retour
     * la liste de menuElement de premier niveau
     * @param int $idMenu
     * @param int|null $parent
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMenuElementByMenuAndParent(int $idMenu, int $parent = null): mixed
    {
        $repo = $this->getRepository(MenuElement::class);
        return $repo->getMenuElementByMenuAndParent($idMenu, $parent);
    }

    /**
     * Retourne une liste de parent valide pour le menuElement choisi
     * @param int $menuId
     * @param int $idElement
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListeParentByMenuElement(int $menuId, int $idElement): array
    {
        /** @var MenuElementRepository $repo */
        $result = $this->getMenuElementByMenuAndParent($menuId);
        return $this->constructListeParent($result, $idElement, []);
    }

    /**
     * Construit de façon recursive une liste de parent valide
     * @param array $menuElements
     * @param int $idElementExclude
     * @param array $return
     * @return array
     */
    private function constructListeParent(array $menuElements, int $idElementExclude, array $return): array
    {
        foreach ($menuElements as $menuElement) {
            /** @var MenuElement $menuElement */

            if ($menuElement->getId() === $idElementExclude) {
                continue;
            }

            foreach ($menuElement->getMenuElementTranslations() as $translation) {
                $return[$menuElement->getId()][$translation->getLocale()] = $translation->getTextLink();
            }

            if (!$menuElement->getChildren()->isEmpty()) {
                $return = $this->constructListeParent($menuElement->getChildren()->toArray(), $idElementExclude, $return);
            }
        }
        return $return;
    }

    /**
     * Met à jour l'ordre de la colum ou row d'une liste de menuElement en fonction de parent
     * @param array $data
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function reorderMenuElement(array $data): void
    {
        $parent = $data['parent'];
        if ($parent === "") {
            $parent = null;
        }
        $listeMenuElements = $this->getMenuElementByMenuAndParent($data['menu'], $parent);

        // trie de la colonne
        if($data['reorderType'] === 'row')
        {
            $tabElement = [];
            foreach ($listeMenuElements as $menuElement) {
                if($data['oldColumn'] === $menuElement->getColumnPosition()) {
                    $tabElement[] = $menuElement;
                }
            }

            $action = OrderEntity::ACTION_AFTER;
            if ($data['oldRow'] > $data['newRow']) {
                $action = OrderEntity::ACTION_BEFORE;
            }

            $orderEntity = new OrderEntity(new ArrayCollection($tabElement), 'rowPosition');
            $idRowReplace = $orderEntity->getIdByOrder($data['newRow']);
            $listeMenuElements = $orderEntity->orderByIdByAction($idRowReplace, $data['id'], $action)
                ->sortByProperty()->reOrderList()->getCollection();

            foreach ($listeMenuElements as $menuElement) {
                $this->save($menuElement);
            }
        }



        /*
        }*/
    }

}
