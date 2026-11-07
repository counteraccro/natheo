<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service sur le menu
 */

namespace App\Service\Admin\Content\Menu;

use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Menu\MenuElement;
use App\Enum\Admin\Content\Menu\MenuCsrfToken;
use App\Enum\Admin\Content\Menu\MenuPosition;
use App\Enum\Admin\Content\Menu\MenuType;
use App\Enum\Admin\Global\SvgIcon;
use App\Repository\Admin\Content\Menu\MenuElementRepository;
use App\Repository\Admin\Content\Menu\MenuRepository;
use App\Security\Voter\ContentVoter;
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
     * @param array $queryParams
     * @param int|null $userId
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams, ?int $userId = null): Paginator
    {
        $repo = $this->getRepository(Menu::class);
        return $repo->getAllPaginate($page, $limit, $queryParams, $userId);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @param int|null $userId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams, ?int $userId = null): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();

        $column = [
            $translator->trans('menu.grid.id', domain: 'menu'),
            $translator->trans('menu.grid.name', domain: 'menu'),
            $translator->trans('menu.grid.type', domain: 'menu'),
            $translator->trans('menu.grid.default', domain: 'menu'),
            $translator->trans('menu.grid.create_at', domain: 'menu'),
            $translator->trans('menu.grid.update_at', domain: 'menu'),
            $translator->trans('menu.grid.author', domain: 'menu'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams, $userId);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Menu $element */

            $action = $this->generateTabAction($element);

            $isDefault = '';
            if ($element->isDefaultMenu()) {
                $isDefault = SvgIcon::MENU->render('inline w-4 h-4');
            }

            $name = htmlspecialchars($element->getName(), ENT_QUOTES);
            $listeType = $this->getListType();
            $listePosition = $this->getListPosition();
            $strType =
                $listePosition[$element->getPosition()] .
                ' / ' .
                $listeType[$element->getPosition()][$element->getType()];

            $strDefault = $translator->trans('menu.grid.default_no', domain: 'menu');
            if ($element->isDefaultMenu()) {
                $strDefault = $translator->trans('menu.grid.default_yes', domain: 'menu');
            }

            $data[] = [
                $translator->trans('menu.grid.id', domain: 'menu') => $element->getId() . ' ' . $isDefault,
                $translator->trans('menu.grid.name', domain: 'menu') => $name,
                $translator->trans('menu.grid.type', domain: 'menu') => $strType,
                $translator->trans('menu.grid.default', domain: 'menu') => $strDefault,
                $translator->trans('menu.grid.create_at', domain: 'menu') => $element
                    ->getCreatedAt()
                    ->format('d/m/y H:i'),
                $translator->trans('menu.grid.update_at', domain: 'menu') => $element
                    ->getUpdateAt()
                    ->format('d/m/y H:i'),
                $translator->trans('menu.grid.author', domain: 'menu') => htmlspecialchars(
                    (string) $element->getUser()?->getLogin(),
                    ENT_QUOTES,
                ),
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
                'id' => $translator->trans('menu.grid.id', domain: 'menu'),
                'name' => $translator->trans('menu.grid.name', domain: 'menu'),
                'type' => $translator->trans('menu.grid.type', domain: 'menu'),
                'defaultMenu' => $translator->trans('menu.grid.default', domain: 'menu'),
                'createdAt' => $translator->trans('menu.grid.create_at', domain: 'menu'),
                'updateAt' => $translator->trans('menu.grid.update_at', domain: 'menu'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Génère le tableau d'actions de la grille des menus, selon les droits (ContentVoter)
     * et les règles du menu par défaut (ni désactivable, ni supprimable ; un menu désactivé ne peut pas le devenir)
     * @param Menu $menu
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Menu $menu): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $security = $this->getSecurity();
        $csrfTokenManager = $this->getCsrfTokenManager();

        $label = htmlspecialchars($menu->getName(), ENT_QUOTES);
        $actions = [];

        if (!$menu->isDefaultMenu() && $security->isGranted(ContentVoter::DISABLE, $menu)) {
            $actionDisabled = [
                'label' => [
                    'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'url' => $router->generate('admin_menu_update_disabled', ['id' => $menu->getId()]),
                'type' => 'put',
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('menu.confirm.disabled.message', ['label' => $label], 'menu'),
                'csrf' => $csrfTokenManager->getToken(MenuCsrfToken::DISABLED->value)->getValue(),
            ];
            if ($menu->isDisabled()) {
                $actionDisabled = [
                    'label' => [
                        'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                        'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                    ],
                    'color' => 'primary',
                    'type' => 'put',
                    'url' => $router->generate('admin_menu_update_disabled', ['id' => $menu->getId()]),
                    'ajax' => true,
                    'csrf' => $csrfTokenManager->getToken(MenuCsrfToken::DISABLED->value)->getValue(),
                ];
            }
            $actions[] = $actionDisabled;
        }

        if (!$menu->isDefaultMenu() && $security->isGranted(ContentVoter::DELETE, $menu)) {
            $actions[] = [
                'label' => [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ],
                'color' => 'danger',
                'type' => 'delete',
                'url' => $router->generate('admin_menu_delete', ['id' => $menu->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('menu.confirm.delete.message', ['label' => $label], 'menu'),
                'csrf' => $csrfTokenManager->getToken(MenuCsrfToken::DELETE->value)->getValue(),
            ];
        }

        if (!$security->isGranted(ContentVoter::EDIT, $menu)) {
            return $actions;
        }

        $actions[] = [
            'label' => [
                'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
            ],
            'color' => 'primary',
            'id' => $menu->getId(),
            'url' => $router->generate('admin_menu_update', ['id' => $menu->getId()]),
            'ajax' => false,
        ];

        if (!$menu->isDefaultMenu() && !$menu->isDisabled()) {
            $actions[] = [
                'label' => [
                    'M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z',
                ],
                'color' => 'success',
                'url' => $router->generate('admin_menu_switch_default', ['id' => $menu->getId()]),
                'type' => 'put',
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('menu.confirm.switch.default.message', ['label' => $label], 'menu'),
                'csrf' => $csrfTokenManager->getToken(MenuCsrfToken::SWITCH_DEFAULT->value)->getValue(),
            ];
        }

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
            MenuPosition::POSITION_HEADER->value => $translator->trans('menu.position.header', domain: 'menu'),
            MenuPosition::POSITION_RIGHT->value => $translator->trans('menu.position.right', domain: 'menu'),
            MenuPosition::POSITION_FOOTER->value => $translator->trans('menu.position.footer', domain: 'menu'),
            MenuPosition::POSITION_LEFT->value => $translator->trans('menu.position.left', domain: 'menu'),
        ];
    }

    /**
     * Retourne une liste de type de menu
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListType(): array
    {
        $translator = $this->getTranslator();
        return [
            MenuPosition::POSITION_HEADER->value => [
                MenuType::HEADER_SIDE_BAR->value => $translator->trans('menu.header.type.sidebar', domain: 'menu'),
                MenuType::HEADER_MENU_DEROULANT->value => $translator->trans(
                    'menu.header.type.dropdown',
                    domain: 'menu',
                ),
                MenuType::HEADER_MENU_DEROULANT_BIG_MENU->value => $translator->trans(
                    'menu.header.type.dropdown_big_menu',
                    domain: 'menu',
                ),
                MenuType::HEADER_MENU_DEROULANT_BIG_MENU_2_COLONNES->value => $translator->trans(
                    'menu.header.type.dropdown_big_menu_2col',
                    domain: 'menu',
                ),
                MenuType::HEADER_MENU_DEROULANT_BIG_MENU_3_COLONNES->value => $translator->trans(
                    'menu.header.type.dropdown_big_menu_3col',
                    domain: 'menu',
                ),
                MenuType::HEADER_MENU_DEROULANT_BIG_MENU_4_COLONNES->value => $translator->trans(
                    'menu.header.type.dropdown_big_menu_4col',
                    domain: 'menu',
                ),
            ],
            MenuPosition::POSITION_LEFT->value => [
                MenuType::LEFT_RIGHT_SIDE_BAR->value => $translator->trans(
                    'menu.left.right.type.sidebar',
                    domain: 'menu',
                ),
                MenuType::LEFT_RIGHT_SIDE_BAR_ACCORDEON->value => $translator->trans(
                    'menu.left.right.type.sidebar_accordion',
                    domain: 'menu',
                ),
            ],
            MenuPosition::POSITION_RIGHT->value => [
                MenuType::LEFT_RIGHT_SIDE_BAR->value => $translator->trans(
                    'menu.left.right.type.sidebar',
                    domain: 'menu',
                ),
                MenuType::LEFT_RIGHT_SIDE_BAR_ACCORDEON->value => $translator->trans(
                    'menu.left.right.type.sidebar_accordion',
                    domain: 'menu',
                ),
            ],
            MenuPosition::POSITION_FOOTER->value => [
                MenuType::FOOTER_1_ROW_RIGHT->value => $translator->trans(
                    'menu.footer.type.row1.right',
                    domain: 'menu',
                ),
                MenuType::FOOTER_1_ROW_CENTER->value => $translator->trans(
                    'menu.footer.type.row1.center',
                    domain: 'menu',
                ),
                MenuType::FOOTER_COLONNES->value => $translator->trans('menu.footer.type.col', domain: 'menu'),
            ],
        ];
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
    public function getMenuElementByMenuAndParent(int $idMenu, ?int $parent = null): mixed
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
        return $this->constructListeParent($result, $idElement, 0, []);
    }

    /**
     * Construit de façon recursive une liste de parent valide
     * @param array $menuElements
     * @param int $idElementExclude
     * @param int $deep
     * @param array $return
     * @return array
     */
    private function constructListeParent(array $menuElements, int $idElementExclude, int $deep, array $return): array
    {
        foreach ($menuElements as $menuElement) {
            /** @var MenuElement $menuElement */

            if ($menuElement->getId() === $idElementExclude) {
                continue;
            }

            foreach ($menuElement->getMenuElementTranslations() as $translation) {
                $return[$menuElement->getId()][$translation->getLocale()] = $translation->getTextLink();
            }
            $return[$menuElement->getId()]['deep'] = $deep;

            if (!$menuElement->getChildren()->isEmpty()) {
                $return = $this->constructListeParent(
                    $menuElement->getChildren()->toArray(),
                    $idElementExclude,
                    $deep + 1,
                    $return,
                );
            }
        }
        return $return;
    }

    /**
     * Retourne le nombre de column et rowMax par column pour la liste en paramètre
     * @param array $menuElements
     * @param array $exclude
     * @return array
     */
    private function getTabColumnAndRowMax(array $menuElements, array $exclude = []): array
    {
        $return = [];
        foreach ($menuElements as $menuElement) {
            /** @var MenuElement $menuElement */

            if (in_array($menuElement->getId(), $exclude)) {
                continue;
            }

            if (!isset($return[$menuElement->getColumnPosition()])) {
                $return[$menuElement->getColumnPosition()] = [
                    'column' => $menuElement->getColumnPosition(),
                    'row' => 0,
                ];
            }

            if ($return[$menuElement->getColumnPosition()]['row'] < $menuElement->getRowPosition()) {
                $return[$menuElement->getColumnPosition()]['row'] = $menuElement->getRowPosition();
            }
        }
        return $return;
    }

    /**
     * Retourne sous la forme id => [nom menu, disabled ou enabled], la liste des menus disponibles
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListMenus(): array
    {
        $return = [];
        $menus = $this->findBy(Menu::class);
        foreach ($menus as $menu) {
            $return[$menu->getId()] = [
                'id' => $menu->getId(),
                'name' => $menu->getName(),
                'disabled' => $menu->isDisabled(),
                'position' => $menu->getPosition(),
            ];
        }
        return $return;
    }

    /**
     * Force tous les menus sauf $excludeId à false pour le champ default pour la position défini
     * @param int $excludeId
     * @param int $position
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function switchDefaultMenuToFalse(int $excludeId, int $position): void
    {
        /** @var MenuRepository $repository */
        $repository = $this->getRepository(Menu::class);
        $repository->unsetDefaultByPosition($excludeId, $position);
    }

    /**
     * Retourne le nombre de pages auxquelles le menu est rattaché
     * @param Menu $menu
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function countPages(Menu $menu): int
    {
        /** @var MenuRepository $repository */
        $repository = $this->getRepository(Menu::class);
        return $repository->countPages($menu);
    }

    /**
     * Indique si un lien externe est autorisé : http(s), mailto, tel, chemin relatif « /… »,
     * ou valeur vide / « # » (élément lié à une page)
     * @param string|null $link
     * @return bool
     */
    public function isAllowedExternalLink(?string $link): bool
    {
        $link = trim((string) $link);
        if ($link === '' || $link === '#') {
            return true;
        }
        // « //domaine » est une URL externe sans schéma : refusée comme chemin relatif
        return (bool) preg_match('#^(https?://|mailto:|tel:|/(?!/))#i', $link);
    }

    /**
     * Indique si au moins un élément (enfants compris) des données reçues a un lien externe non autorisé
     * @param array $menuElements données des menuElements reçues du formulaire
     * @return bool
     */
    public function hasInvalidExternalLink(array $menuElements): bool
    {
        foreach ($menuElements as $menuElement) {
            foreach ($menuElement['menuElementTranslations'] ?? [] as $translation) {
                if (!$this->isAllowedExternalLink($translation['externalLink'] ?? null)) {
                    return true;
                }
            }
            if ($this->hasInvalidExternalLink($menuElement['children'] ?? [])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retourne les positions de menus qui n'ont pas de menu défini par défaut
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getErrorDefaultTypeMenu(): array
    {
        $positions = [
            MenuPosition::POSITION_HEADER->value => [
                'label' => MenuPosition::getStringByPosition(MenuPosition::POSITION_HEADER->value),
                'nb' => 0,
            ],
            MenuPosition::POSITION_FOOTER->value => [
                'label' => MenuPosition::getStringByPosition(MenuPosition::POSITION_FOOTER->value),
                'nb' => 0,
            ],
            MenuPosition::POSITION_LEFT->value => [
                'label' => MenuPosition::getStringByPosition(MenuPosition::POSITION_LEFT->value),
                'nb' => 0,
            ],
        ];

        $result = $this->findBy(Menu::class, ['defaultMenu' => true]);
        foreach ($result as $menu) {
            /** @var Menu $menu */
            if (isset($positions[$menu->getPosition()])) {
                $positions[$menu->getPosition()]['nb'] = $positions[$menu->getPosition()]['nb'] + 1;
            }
        }

        $translator = $this->getTranslator();

        $return = [];
        foreach ($positions as $position) {
            if ($position['nb'] === 0) {
                $return[] = $translator->trans(
                    'menu.error.no.default.' . strtolower($position['label']),
                    domain: 'menu',
                );
            }
            if ($position['nb'] > 1) {
                $return[] = $translator->trans(
                    'menu.error.more.one.default.' . strtolower($position['label']),
                    domain: 'menu',
                );
            }
        }
        return $return;
    }
}
